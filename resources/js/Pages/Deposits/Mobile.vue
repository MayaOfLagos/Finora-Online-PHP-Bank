<script setup>
/**
 * Mobile Deposit Page
 * Pay via payment gateways (Stripe, PayPal, Paystack, etc.)
 */
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Message from 'primevue/message';
import Steps from 'primevue/steps';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    enabledGateways: {
        type: Array,
        default: () => []
    },
    depositLimits: {
        type: Object,
        default: () => ({
            daily: 100000,
            perTransaction: 50000
        })
    },
    todaysTotal: {
        type: Number,
        default: 0
    },
    auth: {
        type: Object,
        default: () => {}
    }
});

const toast = useToast();
const { formatCurrency } = useCurrency();
const page = usePage();
const userCurrency = computed(() => page.props.auth?.currency || 'USD');

const form = useForm({
    bank_account_id: null,
    gateway: null,
    amount: null
});

const currentStep = ref(0);
const isProcessing = ref(false);
const depositComplete = ref(false);
const depositResult = ref(null);
const paymentStatus = ref('idle'); // idle | processing | success | error

const steps = [
    { label: 'Select Account' },
    { label: 'Choose Gateway' },
    { label: 'Enter Amount' },
    { label: 'Complete' }
];

const selectedAccount = computed(() => {
    return props.accounts.find(a => a.id === form.bank_account_id);
});

const selectedGateway = computed(() => {
    return props.enabledGateways.find(g => g.value === form.gateway);
});

const remainingDaily = computed(() => {
    return (props.depositLimits.daily - props.todaysTotal) / 100;
});

const maxAmount = computed(() => {
    return Math.min(
        props.depositLimits.perTransaction / 100,
        remainingDaily.value
    );
});

const isFormValid = computed(() => {
    return form.bank_account_id && 
           form.gateway && 
           form.amount && 
           form.amount > 0 && 
           form.amount <= maxAmount.value;
});

const nextStep = () => {
    if (currentStep.value === 0 && form.bank_account_id) {
        currentStep.value = 1;
    } else if (currentStep.value === 1 && form.gateway) {
        currentStep.value = 2;
    } else if (currentStep.value === 2 && isFormValid.value) {
        submitDeposit();
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

const submitDeposit = () => {
    if (!isFormValid.value) {
        toast.error('Please verify all deposit details', 'Invalid Deposit');
        return;
    }

    isProcessing.value = true;

    form.transform((data) => ({
        ...data,
        amount: Math.round((data.amount || 0) * 100), // Convert dollars to cents
    })).post(route('deposits.mobile.initiate'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Get the deposit data from the flash message
            const depositData = page.props.flash?.deposit || depositResult.value;
            depositResult.value = depositData || form.data();

            if (!depositResult.value?.uuid) {
                isProcessing.value = false;
                toast.error('Could not start payment because the deposit reference was not returned.', 'Deposit Missing');
                return;
            }

            // Debug: Log gateway info
            console.log('Selected gateway code:', form.gateway);
            console.log('All enabled gateways:', props.enabledGateways);
            
            // Known automatic gateways
            const automaticGateways = ['paystack', 'stripe', 'paypal', 'flutterwave', 'razorpay'];
            const isAutomaticGateway = automaticGateways.includes(form.gateway);
            
            console.log('Is automatic gateway?', isAutomaticGateway);
            
            // If not an automatic gateway, treat as manual
            if (!isAutomaticGateway) {
                const manualGateway = props.enabledGateways.find(g => g.value === form.gateway);
                console.log('Found manual gateway:', manualGateway);
                
                if (manualGateway) {
                    showManualDepositInstructions(manualGateway);
                    return;
                }
            }
            
            // Route to appropriate automatic payment gateway
            if (form.gateway === 'paystack') {
                initializePaystack(depositData);
            } else if (form.gateway === 'stripe') {
                initializeStripe(depositData);
            } else if (form.gateway === 'paypal') {
                initializePayPal(depositData);
            } else if (form.gateway === 'flutterwave') {
                initializeFlutterwave(depositData);
            } else if (form.gateway === 'razorpay') {
                initializeRazorpay(depositData);
            } else {
                // Unknown gateway - show error
                isProcessing.value = false;
                const gatewayLabel = props.enabledGateways.find(g => g.value === form.gateway)?.label || form.gateway;
                toast.error(`${gatewayLabel} integration is not yet implemented.`, 'Gateway Not Configured');
            }
        },
        onError: (errors) => {
            isProcessing.value = false;
            toast.error(errors.general || errors.gateway || errors.amount || 'Unable to initiate deposit', 'Deposit Failed');
        }
    });
};

const showManualDepositInstructions = (gateway) => {
    isProcessing.value = false;
    paymentStatus.value = 'success';
    depositComplete.value = true;
    currentStep.value = 3;

    toast.info(`Your deposit request has been created. Follow the instructions for ${gateway.label}`, 'Manual Deposit Instructions');
};

const initializePaystack = (depositData) => {
    // Load Paystack script
    const script = document.createElement('script');
    script.src = 'https://js.paystack.co/v1/inline.js';
    script.onload = () => {
        const paystackConfig = {
            key: depositData?.gateway_public_key || selectedGateway.value?.public_key || 'pk_test_dummy',
            email: props.auth.user?.email || 'user@example.com',
            amount: Math.round(form.amount * 100), // Amount in kobo (assuming NGN smallest unit)
            ref: depositData?.reference || 'ref_' + Date.now(),
            metadata: {
                custom_fields: [
                    {
                        display_name: "Deposit UUID",
                        variable_name: "deposit_uuid",
                        value: depositData?.uuid || depositResult.value?.uuid
                    },
                    {
                        display_name: "User Email",
                        variable_name: "user_email",
                        value: props.auth.user?.email
                    }
                ]
            },
            callback: function(response) {
                // This may fire in addition to onSuccess
                if (response.status === 'success') {
                    confirmPayment(response.reference);
                }
            },
            onClose: function() {
                isProcessing.value = false;
                
                // Check if payment was actually successful before showing cancelled message
                setTimeout(() => {
                    if (paymentStatus.value !== 'success' && paymentStatus.value !== 'processing') {
                        toast.warn('You cancelled the payment. Your deposit was saved as pending.', 'Payment Cancelled');
                    }
                }, 500);
            },
            onSuccess: function(response) {
                // Payment successful - notify backend
                confirmPayment(response.reference);
            }
        };
        
        try {
            const handler = PaystackPop.setup(paystackConfig);
            handler.openIframe();
        } catch (error) {
            isProcessing.value = false;
            toast.error(error.message || 'Could not initialize payment popup', 'Paystack Setup Failed');
        }
    };
    script.onerror = () => {
        isProcessing.value = false;
        toast.error('Failed to load Paystack. Please try again.', 'Payment Gateway Error');
    };
    document.body.appendChild(script);
};

const initializeStripe = async (depositData) => {
    const publishableKey = depositData?.gateway_public_key || selectedGateway.value?.public_key;
    
    if (!publishableKey) {
        isProcessing.value = false;
        toast.error('Stripe Publishable Key is missing. Contact support.', 'Stripe Configuration Error');
        return;
    }

    try {
        // Load Stripe SDK if not already loaded
        if (!window.Stripe) {
            const script = document.createElement('script');
            script.src = 'https://js.stripe.com/v3/';
            script.onload = () => {
                loadStripeElements(publishableKey, depositData);
            };
            script.onerror = () => {
                isProcessing.value = false;
                toast.error('Failed to load Stripe. Please try again.', 'Payment Gateway Error');
            };
            document.body.appendChild(script);
        } else {
            loadStripeElements(publishableKey, depositData);
        }
    } catch (error) {
        isProcessing.value = false;
        toast.error('An error occurred while initializing Stripe.', 'Stripe Error');
    }
};

const loadStripeElements = async (publishableKey, depositData) => {
    try {
        // Initialize Stripe
        const stripe = window.Stripe(publishableKey);

        // Create PaymentIntent on backend
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const intentResponse = await axios.post(route('deposits.mobile.stripe.intent'), {
            deposit_uuid: depositData.uuid
        }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });

        if (!intentResponse.data.success) {
            throw new Error(intentResponse.data.message || 'Failed to create payment intent');
        }

        const { client_secret } = intentResponse.data;

        // Create elements
        const elements = stripe.elements();
        const cardElement = elements.create('card');

        // Create modal for card entry
        const modalOverlay = document.createElement('div');
        modalOverlay.id = 'stripe-modal-overlay';
        modalOverlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; display: flex; align-items: center; justify-content: center;';

        const modalContainer = document.createElement('div');
        modalContainer.id = 'stripe-modal-container';
        modalContainer.style.cssText = 'background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); max-width: 400px; width: 90%;';
        
        const title = document.createElement('h3');
        title.textContent = 'Enter Card Details';
        title.style.cssText = 'margin-top: 0; margin-bottom: 20px; color: #333; font-size: 18px; font-weight: 600;';
        
        const cardContainer = document.createElement('div');
        cardContainer.id = 'card-element';
        cardContainer.style.cssText = 'border: 1px solid #ddd; padding: 10px; border-radius: 4px; margin-bottom: 20px;';
        
        const errorContainer = document.createElement('div');
        errorContainer.id = 'card-errors';
        errorContainer.style.cssText = 'color: #fa755a; margin-bottom: 10px; min-height: 20px;';
        
        const buttonContainer = document.createElement('div');
        buttonContainer.style.cssText = 'display: flex; gap: 10px; justify-content: space-between;';
        
        const payButton = document.createElement('button');
        payButton.textContent = `Pay $${form.amount.toFixed(2)}`;
        payButton.style.cssText = 'flex: 1; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 16px;';
        
        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'Cancel';
        cancelButton.style.cssText = 'flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 16px;';
        
        buttonContainer.appendChild(payButton);
        buttonContainer.appendChild(cancelButton);
        
        modalContainer.appendChild(title);
        modalContainer.appendChild(cardContainer);
        modalContainer.appendChild(errorContainer);
        modalContainer.appendChild(buttonContainer);
        modalOverlay.appendChild(modalContainer);
        document.body.appendChild(modalOverlay);

        // Mount card element
        cardElement.mount('#card-element');

        // Handle card errors
        cardElement.on('change', (event) => {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle pay button click
        payButton.onclick = async () => {
            payButton.disabled = true;
            payButton.textContent = 'Processing...';

            try {
                // Confirm payment with Stripe
                const result = await stripe.confirmCardPayment(client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            email: page.props.auth.user.email
                        }
                    }
                });

                if (result.error) {
                    // Payment failed
                    const displayError = document.getElementById('card-errors');
                    displayError.textContent = result.error.message;
                    payButton.disabled = false;
                    payButton.textContent = `Pay $${form.amount.toFixed(2)}`;
                } else if (result.paymentIntent.status === 'succeeded') {
                    // Payment successful
                    closeStripeModal();
                    confirmPayment(result.paymentIntent.id);
                } else {
                    // Payment requires additional action
                    const displayError = document.getElementById('card-errors');
                    displayError.textContent = 'Payment requires additional authentication. Please try again.';
                    payButton.disabled = false;
                    payButton.textContent = `Pay $${form.amount.toFixed(2)}`;
                }
            } catch (error) {
                const displayError = document.getElementById('card-errors');
                displayError.textContent = 'An error occurred. Please try again.';
                payButton.disabled = false;
                payButton.textContent = `Pay $${form.amount.toFixed(2)}`;
            }
        };

        // Handle cancel button click
        cancelButton.onclick = () => {
            closeStripeModal();
            isProcessing.value = false;
            toast.warn('You cancelled the card payment. Your deposit was saved as pending.', 'Payment Cancelled');
        };

        // Close on overlay click
        modalOverlay.onclick = (e) => {
            if (e.target === modalOverlay) {
                cancelButton.click();
            }
        };

    } catch (error) {
        isProcessing.value = false;
        toast.error(error.message || 'Failed to initialize Stripe payment.', 'Payment Error');
    }
};

const closeStripeModal = () => {
    const overlay = document.getElementById('stripe-modal-overlay');
    if (overlay) overlay.remove();
};

const initializePayPal = (depositData) => {
    // Load PayPal SDK
    const clientId = depositData?.gateway_public_key || selectedGateway.value?.public_key;
    
    if (!clientId) {
        isProcessing.value = false;
        toast.error('PayPal Client ID is missing. Contact support.', 'PayPal Configuration Error');
        return;
    }

    // Check if PayPal SDK is already loaded
    if (window.paypal) {
        renderPayPalButtons(depositData);
        return;
    }

    // Load PayPal SDK script
    const script = document.createElement('script');
    script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=USD`;
    script.onload = () => {
        renderPayPalButtons(depositData);
    };
    script.onerror = () => {
        isProcessing.value = false;
        toast.error('Failed to load PayPal. Please try again.', 'Payment Gateway Error');
    };
    document.body.appendChild(script);
};

const renderPayPalButtons = (depositData) => {
    // Create a container for PayPal buttons if it doesn't exist
    let paypalContainer = document.getElementById('paypal-button-container');
    
    if (!paypalContainer) {
        paypalContainer = document.createElement('div');
        paypalContainer.id = 'paypal-button-container';
        paypalContainer.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); min-width: 300px;';
        document.body.appendChild(paypalContainer);
        
        // Add overlay
        const overlay = document.createElement('div');
        overlay.id = 'paypal-overlay';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998;';
        overlay.onclick = () => {
            closePayPalModal();
        };
        document.body.appendChild(overlay);
    }

    // Render PayPal buttons
    window.paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: form.amount.toFixed(2),
                        currency_code: userCurrency.value
                    },
                    description: `Deposit to ${selectedAccount.value?.account_type?.name}`,
                    custom_id: depositData?.uuid
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Payment successful - confirm with backend
                closePayPalModal();
                confirmPayment(details.id); // PayPal transaction ID
            });
        },
        onCancel: function(data) {
            closePayPalModal();
            isProcessing.value = false;
            toast.warn('You cancelled the PayPal payment. Your deposit was saved as pending.', 'Payment Cancelled');
        },
        onError: function(err) {
            closePayPalModal();
            isProcessing.value = false;
            toast.error('An error occurred during payment. Please try again.', 'PayPal Error');
        }
    }).render('#paypal-button-container');
};

const closePayPalModal = () => {
    const container = document.getElementById('paypal-button-container');
    const overlay = document.getElementById('paypal-overlay');
    if (container) container.remove();
    if (overlay) overlay.remove();
};

const initializeFlutterwave = async (depositData) => {
    try {
        // Create payment link via backend
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const linkResponse = await axios.post(route('deposits.mobile.flutterwave.link'), {
            deposit_uuid: depositData.uuid
        }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });

        if (!linkResponse.data.success) {
            throw new Error(linkResponse.data.message || 'Failed to create payment link');
        }

        // Load Flutterwave SDK
        if (!window.FlutterwaveCheckout) {
            const script = document.createElement('script');
            script.src = 'https://checkout.flutterwave.com/v3.js';
            script.onload = () => {
                renderFlutterwaveForm(depositData, linkResponse.data);
            };
            script.onerror = () => {
                isProcessing.value = false;
                toast.error('Failed to load Flutterwave. Please try again.', 'Payment Gateway Error');
            };
            document.body.appendChild(script);
        } else {
            renderFlutterwaveForm(depositData, linkResponse.data);
        }
    } catch (error) {
        isProcessing.value = false;
        toast.error(error.message || 'Failed to initialize Flutterwave payment.', 'Flutterwave Error');
    }
};

const renderFlutterwaveForm = (depositData, config) => {
    window.FlutterwaveCheckout({
        public_key: config.public_key,
        tx_ref: config.tx_ref,
        amount: config.amount,
        currency: config.currency,
        payment_options: 'card,banktransfer',
        customer: {
            email: config.customer_email,
            name: config.customer_name,
        },
        customizations: {
            title: 'Finora Bank Deposit',
            description: `Fund your ${selectedAccount.value?.account_type?.name}`,
            logo: '/logo.png'
        },
        callback: (data) => {
            // The Flutterwave inline callback returns the transaction reference
            const transactionRef = data.flw_ref || data.tx_ref || data.transaction_id || data.id;
            
            if (!transactionRef) {
                isProcessing.value = false;
                toast.error('Transaction reference is missing. Please contact support.', 'Payment Reference Missing');
                return;
            }
            
            // Close Flutterwave modal programmatically
            closeFlutterwaveModal();
            
            // Confirm payment via backend
            confirmPayment(transactionRef);
        },
        onclose: () => {
            // Only show cancellation if payment wasn't already confirmed
            if (paymentStatus.value !== 'processing' && paymentStatus.value !== 'success') {
                isProcessing.value = false;
                toast.warn('You cancelled the Flutterwave payment. Your deposit was saved as pending.', 'Payment Cancelled');
            }
        }
    });
};

const closeFlutterwaveModal = () => {
    // Remove Flutterwave modal/iframe from DOM
    const flwModal = document.querySelector('.flutterwave-modal-container') || 
                     document.querySelector('.flutterwave-modal') ||
                     document.querySelector('[id*="flutterwave"]') ||
                     document.querySelector('iframe[src*="flutterwave"]');
    
    if (flwModal) {
        flwModal.remove();
    }
    
    // Also remove any overlay
    const overlay = document.querySelector('.flutterwave-overlay') ||
                    document.querySelector('[class*="flw-modal-overlay"]');
    
    if (overlay) {
        overlay.remove();
    }
};

const initializeRazorpay = async (depositData) => {
    try {
        // Create Razorpay order via backend
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const orderResponse = await axios.post(route('deposits.mobile.razorpay.order'), {
            deposit_uuid: depositData.uuid
        }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });

        if (!orderResponse.data.success) {
            throw new Error(orderResponse.data.message || 'Failed to create Razorpay order');
        }

        // Load Razorpay SDK
        if (!window.Razorpay) {
            const script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.onload = () => {
                openRazorpayCheckout(depositData, orderResponse.data);
            };
            script.onerror = () => {
                isProcessing.value = false;
                toast.error('Failed to load Razorpay. Please try again.', 'Payment Gateway Error');
            };
            document.body.appendChild(script);
        } else {
            openRazorpayCheckout(depositData, orderResponse.data);
        }
    } catch (error) {
        isProcessing.value = false;
        toast.error(error.message || 'Failed to initialize Razorpay payment.', 'Razorpay Error');
    }
};

const openRazorpayCheckout = (depositData, config) => {
    const razorpay = new window.Razorpay({
        key: config.key_id,
        amount: config.amount,
        currency: config.currency,
        order_id: config.order_id,
        name: 'Finora Bank',
        description: `Deposit to ${selectedAccount.value?.account_type?.name}`,
        customer_id: page.props.auth.user.id,
        prefill: {
            name: config.customer_name,
            email: config.customer_email,
        },
        handler: function(response) {
            // Payment successful
            confirmPayment(response.razorpay_payment_id);
        },
        modal: {
            ondismiss: function() {
                isProcessing.value = false;
                toast.warn('You cancelled the Razorpay payment. Your deposit was saved as pending.', 'Payment Cancelled');
            }
        },
        theme: {
            color: '#007bff'
        }
    });

    razorpay.open();
};

const confirmPayment = (reference) => {
    paymentStatus.value = 'processing';
    depositComplete.value = true;

    if (!depositResult.value?.uuid) {
        paymentStatus.value = 'error';
        isProcessing.value = false;
        toast.error('Payment reference is missing. Please contact support with your Paystack receipt.', 'Confirmation Failed');
        return;
    }

    try {
        const url = route('deposits.mobile.callback', {
            mobileDeposit: depositResult.value.uuid
        });

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                         document.head.querySelector('meta[name="csrf-token"]')?.content;

        axios.post(url, {
            gateway_reference: reference
        }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            paymentStatus.value = 'success';
            depositResult.value = response.data?.deposit ?? depositResult.value;
            currentStep.value = 3;
            isProcessing.value = false;
            toast.success('Your deposit has been completed', 'Payment Successful');
        }).catch(error => {
            paymentStatus.value = 'error';
            isProcessing.value = false;
            
            const errorMsg = error.response?.data?.message || 'Payment received but confirmation failed. Please contact support.';
            
            toast.error(errorMsg, 'Confirmation Failed');
        });
    } catch (error) {
        paymentStatus.value = 'error';
        isProcessing.value = false;
        toast.error('Could not confirm payment due to a routing error. Please contact support.', 'Confirmation Failed');
    }
};

const startNewDeposit = () => {
    form.reset();
    currentStep.value = 0;
    depositComplete.value = false;
    depositResult.value = null;
    paymentStatus.value = 'idle';
};
</script>

<template>
    <Head title="Mobile Deposit" />

    <DashboardLayout title="Mobile Deposit">
        <div class="max-w-2xl mx-auto">
            <Link
                href="/deposits"
                class="inline-flex items-center gap-2 mb-6 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <i class="pi pi-arrow-left"></i>
                Back to Deposits
            </Link>

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Mobile Deposit
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pay via secure payment gateway
                </p>
            </div>
        </div>

        <!-- Deposit Form -->
        <div class="max-w-2xl mx-auto">
            <!-- Steps -->
            <Card v-if="!depositComplete" class="mb-6">
                <template #content>
                    <Steps :model="steps" :activeStep="currentStep" :readonly="true" />
                </template>
            </Card>

            <!-- Step 1: Select Account -->
            <Card v-if="currentStep === 0 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-wallet text-primary-500"></i>
                        Select Deposit Account
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div
                            v-for="account in props.accounts"
                            :key="account.id"
                            @click="form.bank_account_id = account.id"
                            class="p-4 transition-all duration-300 border-2 rounded-lg cursor-pointer"
                            :class="[
                                form.bank_account_id === account.id
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-full transition-all"
                                        :class="[
                                            form.bank_account_id === account.id
                                                ? 'bg-green-500 text-white'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                        ]"
                                    >
                                        <i
                                            class="pi"
                                            :class="[
                                                form.bank_account_id === account.id
                                                    ? 'pi-check'
                                                    : 'pi-wallet'
                                            ]"
                                        ></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ account.account_type?.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            ****{{ account.account_number.slice(-4) }}
                                        </p>
                                    </div>
                                </div>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(account.balance, account.currency) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link href="/deposits">
                                <Button label="Cancel" outlined />
                            </Link>
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!form.bank_account_id"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 2: Choose Gateway -->
            <Card v-if="currentStep === 1 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-credit-card text-primary-500"></i>
                        Choose Payment Method
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Depositing to <strong>{{ selectedAccount?.account_type?.name }}</strong>
                        </p>

                        <!-- Automatic Payment Gateways -->
                        <div v-if="enabledGateways.filter(g => g.type === 'automatic').length > 0" class="space-y-3">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Automatic Payment Methods</h4>
                            <div
                                v-for="gateway in enabledGateways.filter(g => g.type === 'automatic')"
                                :key="gateway.value"
                                @click="form.gateway = gateway.value"
                                class="p-4 transition-all duration-300 border-2 rounded-lg cursor-pointer"
                                :class="[
                                    form.gateway === gateway.value
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 rounded-lg transition-all"
                                        :class="[
                                            form.gateway === gateway.value
                                                ? 'bg-blue-500 text-white'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                        ]"
                                    >
                                        <i :class="['pi', form.gateway === gateway.value ? 'pi-check' : 'pi-credit-card']"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ gateway.label }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Instant payment processing
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div v-if="enabledGateways.filter(g => g.type === 'automatic').length > 0 && enabledGateways.filter(g => g.type === 'manual').length > 0" class="border-t border-gray-200 dark:border-gray-700"></div>

                        <!-- Manual Payment Methods -->
                        <div v-if="enabledGateways.filter(g => g.type === 'manual').length > 0" class="space-y-3">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Manual Deposit Methods</h4>
                            <div
                                v-for="gateway in enabledGateways.filter(g => g.type === 'manual')"
                                :key="gateway.value"
                                @click="form.gateway = gateway.value"
                                class="p-4 transition-all duration-300 border-2 rounded-lg cursor-pointer"
                                :class="[
                                    form.gateway === gateway.value
                                        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 rounded-lg transition-all"
                                        :class="[
                                            form.gateway === gateway.value
                                                ? 'bg-orange-500 text-white'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                        ]"
                                    >
                                        <i :class="['pi', form.gateway === gateway.value ? 'pi-check' : 'pi-banknotes']"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ gateway.label }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ gateway.description || 'Manual transfer required' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Message severity="info" :closable="false" class="mt-4">
                            <p class="text-sm" v-if="selectedGateway?.type === 'automatic'">
                                You'll be redirected to {{ selectedGateway?.label }} to complete payment securely.
                            </p>
                            <p class="text-sm" v-else-if="selectedGateway?.type === 'manual'">
                                You'll receive instructions for {{ selectedGateway?.label }} after confirming your deposit.
                            </p>
                        </Message>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!form.gateway"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 3: Enter Amount -->
            <Card v-if="currentStep === 2 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-dollar text-primary-500"></i>
                        Enter Deposit Amount
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                v-model="form.amount"
                                mode="currency"
                                :currency="userCurrency"
                                locale="en-US"
                                placeholder="0.00"
                                class="w-full"
                                :min="1"
                                :max="maxAmount"
                            />
                            <div class="flex justify-between mt-2 text-xs text-gray-500">
                                <span>Max per transaction: {{ formatCurrency(depositLimits.perTransaction, userCurrency) }}</span>
                                <span>Available today: {{ formatCurrency(remainingDaily * 100, userCurrency) }}</span>
                            </div>
                        </div>

                        <Message v-if="remainingDaily <= 0" severity="error" :closable="false">
                            <p class="text-sm">Daily deposit limit reached</p>
                        </Message>

                        <Message severity="info" :closable="false">
                            <p class="text-sm"><strong>Gateway:</strong> {{ selectedGateway?.label }}</p>
                            <p class="text-sm mt-2"><strong>Account:</strong> {{ selectedAccount?.account_type?.name }} (****{{ selectedAccount?.account_number.slice(-4) }})</p>
                        </Message>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Proceed to Payment"
                                icon="pi pi-send"
                                :disabled="!isFormValid || isProcessing"
                                :loading="isProcessing"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 4: Complete -->
            <Card v-if="depositComplete" class="shadow-lg">
                <template #content>
                    <div class="py-8">
                        <!-- Automatic Gateway Success/Status -->
                        <div v-if="selectedGateway?.type === 'automatic'" class="text-center">
                            <div
                                class="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-full"
                                :class="[
                                    paymentStatus === 'success' ? 'bg-green-100 dark:bg-green-900/30' :
                                    paymentStatus === 'error' ? 'bg-red-100 dark:bg-red-900/30' :
                                    'bg-blue-100 dark:bg-blue-900/30'
                                ]"
                            >
                                <i
                                    class="text-4xl"
                                    :class="[
                                        paymentStatus === 'success' ? 'pi pi-check-circle text-green-600 dark:text-green-400' :
                                        paymentStatus === 'error' ? 'pi pi-times-circle text-red-600 dark:text-red-400' :
                                        'pi pi-spin pi-spinner text-blue-600 dark:text-blue-400'
                                    ]"
                                ></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ paymentStatus === 'success' ? 'Deposit Completed' : paymentStatus === 'error' ? 'Confirmation Failed' : 'Processing Payment' }}
                            </h3>
                            <p class="mt-2 text-gray-500">
                                {{ paymentStatus === 'success'
                                    ? 'Your deposit has been confirmed successfully.'
                                    : paymentStatus === 'error'
                                        ? 'Payment was received, but we could not confirm it automatically. Please contact support.'
                                        : `Confirming your payment with ${selectedGateway?.label}...`
                                }}
                            </p>
                        </div>

                        <!-- Manual Deposit Instructions -->
                        <div v-else-if="selectedGateway?.type === 'manual'" class="text-center">
                            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-full bg-orange-100 dark:bg-orange-900/30">
                                <i class="text-4xl pi pi-info-circle text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Follow These Instructions
                            </h3>
                            <p class="mt-2 text-gray-500">
                                Your deposit request has been created. Please follow the instructions below to complete your deposit via {{ selectedGateway?.label }}.
                            </p>

                            <!-- Instructions -->
                            <Message severity="warning" :closable="false" class="mt-6 text-left">
                                <div class="space-y-3">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ selectedGateway?.label }} Instructions:
                                    </p>
                                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                        {{ selectedGateway?.description || 'Please contact support for instructions' }}
                                    </div>
                                </div>
                            </Message>

                            <!-- Details Table -->
                            <div v-if="selectedGateway?.instructions && Object.keys(selectedGateway.instructions).length > 0" 
                                 class="mt-6 p-4 rounded-lg bg-gray-50 dark:bg-gray-800 text-left">
                                <p class="font-semibold text-gray-900 dark:text-white mb-3">Bank Details:</p>
                                <div class="space-y-2">
                                    <div v-for="(value, key) in selectedGateway.instructions" :key="key" class="flex justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                        <span class="text-gray-500 capitalize">{{ key.replace(/_/g, ' ') }}</span>
                                        <span class="font-mono text-gray-900 dark:text-white">{{ value }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Reference Code -->
                            <div class="mt-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Your Deposit Reference:</p>
                                <p class="text-lg font-mono font-bold text-gray-900 dark:text-white">
                                    {{ depositResult?.uuid || 'Reference loading...' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-2">Please include this reference when making your deposit</p>
                            </div>

                            <Message severity="info" :closable="false" class="mt-6">
                                <p class="text-sm">Once you've completed the transfer, your deposit will be verified and credited to your account.</p>
                            </Message>
                        </div>

                        <!-- Summary for Both Types -->
                        <div class="p-4 mt-6 text-left rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Amount</span>
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(form.amount * 100, userCurrency) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Method</span>
                                    <span class="text-gray-900 dark:text-white">{{ selectedGateway?.label }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Account</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ selectedAccount?.account_type?.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-3 mt-6">
                            <Button
                                v-if="paymentStatus !== 'processing'"
                                label="New Deposit"
                                icon="pi pi-plus"
                                outlined
                                @click="startNewDeposit"
                            />
                            <Link href="/deposits">
                                <Button label="Back to Deposits" icon="pi pi-arrow-left" />
                            </Link>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
