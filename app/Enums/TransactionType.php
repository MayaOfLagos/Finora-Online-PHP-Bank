<?php

namespace App\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Transfer = 'transfer';
    case Refund = 'refund';
    case Adjustment = 'adjustment';
    case Credit = 'credit';
    case Debit = 'debit';
    case WireTransfer = 'wire_transfer';
    case DomesticTransfer = 'domestic_transfer';
    case InternalTransfer = 'internal_transfer';
    case AccountTransfer = 'account_transfer';
    case MobileDeposit = 'mobile_deposit';
    case CryptoDeposit = 'crypto_deposit';
    case CheckDeposit = 'check_deposit';
    case LoanRepayment = 'loan_repayment';
    case GrantDisbursement = 'grant_disbursement';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Deposit',
            self::Withdrawal => 'Withdrawal',
            self::Transfer => 'Transfer',
            self::Refund => 'Refund',
            self::Adjustment => 'Adjustment',
            self::Credit => 'Credit',
            self::Debit => 'Debit',
            self::WireTransfer => 'Wire Transfer',
            self::DomesticTransfer => 'Domestic Transfer',
            self::InternalTransfer => 'Internal Transfer',
            self::AccountTransfer => 'Account Transfer',
            self::MobileDeposit => 'Mobile Deposit',
            self::CryptoDeposit => 'Crypto Deposit',
            self::CheckDeposit => 'Check Deposit',
            self::LoanRepayment => 'Loan Repayment',
            self::GrantDisbursement => 'Grant Disbursement',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Deposit, self::Credit, self::Refund, self::GrantDisbursement => 'success',
            self::Withdrawal, self::Debit, self::LoanRepayment => 'danger',
            self::Transfer, self::WireTransfer, self::DomesticTransfer, self::InternalTransfer, self::AccountTransfer => 'info',
            self::Adjustment => 'warning',
            self::MobileDeposit, self::CryptoDeposit, self::CheckDeposit => 'secondary',
        };
    }
}
