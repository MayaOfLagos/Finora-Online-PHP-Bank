# Quick Reference Guide - Beneficiary Field Management

## 🎯 For Developers

### **Using Dynamic Fields in Controllers**

```php
use App\Filament\Helpers\BeneficiaryFieldMapper;
use App\Models\BeneficiaryFieldTemplate;

// 1. Get fields for a transfer type
$fields = BeneficiaryFieldTemplate::forTransferType('wire');

// 2. Map to Filament components (in Filament forms)
$components = BeneficiaryFieldMapper::mapToFilamentComponents('wire');

// 3. Get validation rules
$rules = BeneficiaryFieldMapper::getValidationRules('wire');

// 4. Extract beneficiary data from request
$beneficiaryData = BeneficiaryFieldMapper::extractBeneficiaryData($request->all(), 'wire');

// 5. Store in transfer
WireTransfer::create([
    'user_id' => auth()->id(),
    'beneficiary_name' => $request->beneficiary_name,
    'beneficiary_data' => $beneficiaryData, // Stores all dynamic fields
    // ... other fields
]);
```

### **Querying Beneficiary Data**

```php
// Get all wire transfers with specific country
WireTransfer::whereJsonContains('beneficiary_data->beneficiary_country', 'US')->get();

// Get transfer with custom field value
WireTransfer::where('beneficiary_data->custom_field', 'value')->first();

// Access beneficiary data
$transfer = WireTransfer::find(1);
$country = $transfer->beneficiary_data['beneficiary_country'] ?? null;
```

---

## 🎨 For Admins

### **Access Beneficiary Fields**
1. Login to admin panel: `http://127.0.0.1:8000/admin`
2. Navigate to: **Settings → Beneficiary Fields**

### **Filter by Tabs**
- **All Fields** - View all fields across all transfer types
- **Wire Transfer** - Fields for international wire transfers
- **Domestic Transfer** - Fields for local bank transfers
- **Internal Transfer** - Fields for within-bank transfers
- **Enabled** - Only active fields
- **Disabled** - Inactive fields

### **Create New Field**
1. Click **"Create"** button
2. Fill form:
   - **Field Key:** Unique identifier (e.g., `beneficiary_phone`)
   - **Field Label:** Display name (e.g., "Phone Number")
   - **Field Type:** text, textarea, select, or country
   - **Applies To:** wire, domestic, internal, or all
   - **Required Field:** Toggle on/off
   - **Enabled:** Toggle on/off
   - **Display Order:** Numeric (lower = higher priority)
   - **Placeholder:** Optional hint text
   - **Helper Text:** Optional help message
   - **Select Options:** For select type only (key-value pairs)
3. Click **"Create"**

### **Edit Existing Field**
1. Click row in table
2. Modify fields
3. Click **"Save changes"**

### **Reorder Fields**
- Set **Display Order** value
- Lower numbers appear first in forms
- Example: 1, 2, 3, 4...

### **Enable/Disable Fields**
- Toggle **"Enabled"** switch
- Disabled fields won't appear in transfer forms
- Data is preserved if field is disabled later

---

## 📋 Field Types Reference

| Type | Description | Example Use Case | Validation |
|------|-------------|------------------|------------|
| **text** | Single-line input | Account number, name | max:255 |
| **textarea** | Multi-line input | Address, notes | max:1000 |
| **select** | Dropdown list | Bank branch, relationship | Must match option |
| **country** | Country selector | Beneficiary country | 2-char ISO code |

---

## 🔍 Search & Filter

### **Table Filters:**
- **Transfer Type:** wire, domestic, internal, all
- **Field Type:** text, textarea, select, country
- **Status:** enabled, disabled

### **Search:**
- Searches: Field Key, Field Label
- Real-time filtering

---

## 💡 Best Practices

### **Field Naming:**
- Use `snake_case` for field keys
- Use descriptive names: `beneficiary_phone` not `phone`
- Prefix with entity: `beneficiary_`, `sender_`, `receiver_`

### **Field Organization:**
- Group related fields with display_order
- Name: 1, Account: 2, Bank: 3, Address: 4, etc.
- Leave gaps (10, 20, 30) for future insertions

### **Required Fields:**
- Only mark truly essential fields as required
- Consider making optional with helper text encouragement
- Test user experience with required fields

### **Helper Text:**
- Use for complex fields
- Provide format examples: "Format: +1234567890"
- Explain why field is needed

### **Select Options:**
- Keep option lists short (< 20 items)
- Use country type for country lists
- Format: `key => label` (e.g., `savings => Savings Account`)

---

## 🛠️ Troubleshooting

### **Field Not Showing in Form:**
✅ Check if field is **Enabled**  
✅ Verify **Applies To** matches transfer type  
✅ Clear cache: `php artisan optimize:clear`  
✅ Check **Display Order** is set

### **Validation Errors:**
✅ Verify **Required** setting matches user input  
✅ Check field type validation rules  
✅ For select: ensure submitted value matches option key  
✅ For country: ensure 2-character ISO code

### **Data Not Saving:**
✅ Ensure `beneficiary_data` in model's `$fillable`  
✅ Check `beneficiary_data` cast to `array` in model  
✅ Verify `beneficiary_data` column exists in database

---

## 📞 Support

### **Common Questions:**

**Q: Can I add fields without coding?**  
A: Yes! Use the admin panel to create fields dynamically.

**Q: Can I have different fields for different transfer types?**  
A: Yes! Use the "Applies To" setting.

**Q: What happens to old data if I disable a field?**  
A: Data is preserved in `beneficiary_data` JSON, just hidden from forms.

**Q: Can I reuse field keys across transfer types?**  
A: Yes! The unique constraint is `field_key + applies_to`.

**Q: How many fields can I add?**  
A: Unlimited! Stored in JSON column.

**Q: Can I add validation rules?**  
A: Yes! Validation is automatic based on field type. Custom rules require code.

---

## 🔐 Security Notes

- Field keys are sanitized (alphanumeric + underscore only)
- Field validation prevents injection attacks
- Helper text is escaped in HTML output
- JSON storage protects against SQL injection
- Admin-only access to field management

---

## 📈 Performance Tips

- Enable caching for field queries (consider Redis)
- Index `applies_to` and `is_enabled` columns
- Limit number of fields per form (< 15 recommended)
- Use select type for finite options (better UX)
- Monitor `beneficiary_data` column size

---

## 🎓 Training Resources

### **Video Tutorials:** (To be created)
- Creating your first custom field
- Managing transfer-specific fields
- Advanced field configuration

### **Documentation Links:**
- Filament Forms: https://filamentphp.com/docs/forms
- Laravel Eloquent JSON: https://laravel.com/docs/eloquent-mutators#json-casting
- Bootstrap Modal: For frontend integration

---

**Last Updated:** 2026-01-24  
**Version:** 1.0.0  
**Maintained By:** Development Team
