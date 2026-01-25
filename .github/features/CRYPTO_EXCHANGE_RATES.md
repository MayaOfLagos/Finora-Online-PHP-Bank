# Crypto Exchange Rate System

## Overview
The crypto deposit system now includes automatic USD ↔ Crypto conversion using:
1. **CoinGecko API** for live real-time rates (free, no API key needed)
2. **Manual fallback rates** set by admin in case API fails

## How It Works

### User Experience
1. **Select Cryptocurrency** - Choose BTC, ETH, USDT, etc.
2. **Enter USD Amount** - Type in dollars (e.g., $2,000)
3. **Auto-Calculate Crypto** - System automatically converts to BTC amount (e.g., 0.021053)
4. **Or Enter Crypto First** - Type BTC amount and USD auto-calculates

### Exchange Rate Flow
```
1. User selects cryptocurrency
   ↓
2. System fetches exchange rate:
   - Try CoinGecko API (if coingecko_id exists)
   - Cache for 5 minutes
   - Fallback to manual rate if API fails
   ↓
3. Display: "1 BTC = $95,000.00"
   ↓
4. User enters USD → Auto-convert to crypto
   OR
   User enters crypto → Auto-convert to USD
```

## Admin Configuration

### Setting Up Cryptocurrencies

#### Option 1: Use CoinGecko (Recommended)
1. Go to **Admin Panel → Settings → Cryptocurrencies**
2. Edit a cryptocurrency (e.g., Bitcoin)
3. Set these fields:
   - **CoinGecko API ID**: `bitcoin` (for BTC), `ethereum` (for ETH)
   - **Manual Exchange Rate**: `95000.00` (fallback only)
4. System will fetch live rates from CoinGecko
5. Manual rate used only if API fails

#### Option 2: Manual Rates Only
1. Leave **CoinGecko API ID** empty
2. Set **Manual Exchange Rate**: e.g., `95000.00`
3. System will use your manual rate always

### CoinGecko IDs Reference
Common cryptocurrencies:
- Bitcoin: `bitcoin`
- Ethereum: `ethereum`
- Tether: `tether`
- USD Coin: `usd-coin`
- Binance Coin: `binancecoin`
- Cardano: `cardano`
- Dogecoin: `dogecoin`
- XRP: `ripple`

Find more at: https://www.coingecko.com/

## Technical Details

### Files Modified
1. **Database**
   - Migration: `add_exchange_rate_to_cryptocurrencies_table.php`
   - New columns: `exchange_rate_usd`, `coingecko_id`, `description`

2. **Backend**
   - `app/Services/CryptoExchangeRateService.php` - API integration
   - `app/Models/Cryptocurrency.php` - Helper methods
   - `app/Http/Controllers/CryptoDepositController.php` - API endpoint
   - `routes/web.php` - New route for exchange rates

3. **Frontend**
   - `resources/js/Pages/Deposits/Crypto.vue` - Auto-conversion UI

4. **Admin Panel**
   - `app/Filament/Resources/Cryptocurrencies/Schemas/CryptocurrencyForm.php` - Form fields

### API Endpoints
```
GET /deposits/crypto/{cryptocurrency}/wallet
→ Returns wallet addresses + current exchange rate

GET /deposits/crypto/{cryptocurrency}/rate
→ Returns only exchange rate (for manual refresh)
```

### Caching
- Exchange rates cached for **5 minutes** (300 seconds)
- Reduces API calls to CoinGecko
- Clear cache: `php artisan cache:clear`

### Rate Limit
- CoinGecko free tier: **50 calls/minute**
- With 5-minute cache, can handle ~250 users/minute

## Testing

### Test Live Rates
1. Make sure Bitcoin has `coingecko_id = bitcoin`
2. Clear cache: `php artisan cache:clear`
3. Visit crypto deposit page
4. Select Bitcoin
5. Should show: "1 BTC = $[live_price]"

### Test Fallback
1. Set invalid `coingecko_id = invalid_test`
2. Set `exchange_rate_usd = 50000`
3. Visit crypto deposit page
4. Should use manual rate: "1 BTC = $50,000.00"

### Test Conversion
1. Enter USD: `2000`
2. Should auto-calculate BTC: `0.021053` (if rate is $95,000)
3. Edit BTC: `0.5`
4. Should auto-calculate USD: `$47,500.00`

## Troubleshooting

### "Exchange rate unavailable"
**Causes:**
- No `coingecko_id` set
- No `exchange_rate_usd` set
- CoinGecko API down + no manual rate

**Fix:**
- Set manual `exchange_rate_usd` in admin panel

### Auto-conversion not working
**Fix:**
1. Hard refresh browser: `Cmd+Shift+R` (Mac) or `Ctrl+Shift+R` (Windows)
2. Clear cache: `php artisan cache:clear`
3. Rebuild assets: `npm run build`

### Wrong rates displayed
**Fix:**
1. Check CoinGecko ID is correct
2. Clear cache: `php artisan cache:clear`
3. Update manual rate as backup

## Example: Adding New Crypto

### Via Admin Panel
1. Go to **Cryptocurrencies → Create**
2. Fill in:
   - Name: `Solana`
   - Symbol: `SOL`
   - Network: `Solana`
   - CoinGecko ID: `solana`
   - Exchange Rate USD: `150.00` (fallback)
   - Description: `High-performance blockchain`
3. Add wallet address via **Wallets** tab
4. Test on deposit page

### Via Seeder (Recommended for bulk)
```php
Cryptocurrency::create([
    'name' => 'Solana',
    'symbol' => 'SOL',
    'network' => 'Solana',
    'coingecko_id' => 'solana',
    'exchange_rate_usd' => 150.00,
    'description' => 'High-performance blockchain',
    'is_active' => true,
]);
```

## Security Notes

- ✅ Rates cached to prevent API abuse
- ✅ Manual fallback for reliability
- ✅ Input validation on amounts
- ✅ Daily/transaction limits enforced
- ⚠️ Free CoinGecko API = no authentication needed
- ⚠️ Consider upgrading to CoinGecko Pro for production

## Future Enhancements

- [ ] Support multiple fiat currencies (EUR, GBP)
- [ ] Historical rate charts
- [ ] Price alerts for admin
- [ ] Automatic rate refresh button for users
- [ ] Support other APIs (CoinMarketCap, Binance)
