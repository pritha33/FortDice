// Employee.js - Employee dashboard functionality

function decryptCustomerData(accountNumber) {
    const securityKey = prompt('Please enter your security key to decrypt this data:');
    if (!securityKey) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="decrypt">
        <input type="hidden" name="account_number" value="${accountNumber}">
        <input type="hidden" name="security_key" value="${securityKey}">
    `;
    document.body.appendChild(form);
    form.submit();
}