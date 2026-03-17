// manage_customers.js

function initCustomerModule() {
    const contentDiv = document.getElementById('admin-content');
    
    // Build the shell for the customer module
    contentDiv.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Customer Management</h2>
            <button onclick="openCustomerModal()" class="btn" style="background: var(--accent); color: #151515; border: none; font-weight: 600;">+ Add New Customer</button>
        </div>
        <div id="customer-alert" style="display: none; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem;"></div>
        <div style="overflow-x: auto; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; background: var(--graphite);">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); background: rgba(0,0,0,0.3);">
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Name</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Email</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Phone</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Joined</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="customer-table-body">
                    <tr><td colspan="5" style="padding: 2rem; text-align: center; color: var(--muted);">Loading customers...</td></tr>
                </tbody>
            </table>
        </div>
    `;

    fetchCustomers();
}

async function fetchCustomers() {
    try {
        const response = await fetch('api_customers.php');
        const data = await response.json();
        const tbody = document.getElementById('customer-table-body');

        if (data.success) {
            tbody.innerHTML = '';
            if (data.customers.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="padding: 2rem; text-align: center; color: var(--muted);">No customers found.</td></tr>`;
                return;
            }

            data.customers.forEach(customer => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid rgba(255, 255, 255, 0.04)';
                
                // Format date
                const joinedDate = new Date(customer.created_at).toLocaleDateString();

                tr.innerHTML = `
                    <td style="padding: 1rem; font-weight: 500;">${customer.first_name} ${customer.last_name}</td>
                    <td style="padding: 1rem; color: var(--muted);">${customer.email}</td>
                    <td style="padding: 1rem; color: var(--muted);">${customer.phone || 'N/A'}</td>
                    <td style="padding: 1rem; color: var(--muted);">${joinedDate}</td>
                    <td style="padding: 1rem; text-align: right; gap: 0.5rem; display: flex; justify-content: flex-end;">
                        <button onclick='openCustomerModal(${JSON.stringify(customer)})' style="padding: 0.4rem 0.8rem; background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">Edit</button>
                        <button onclick="deleteCustomer(${customer.user_id})" style="padding: 0.4rem 0.8rem; background: transparent; border: 1px solid #ff6b6b; color: #ff6b6b; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (err) {
        showAlert('Failed to load customers.', 'error');
    }
}

function openCustomerModal(customer = null) {
    // Remove existing modal if any
    const existing = document.getElementById('customer-modal');
    if (existing) existing.remove();

    const isEdit = customer !== null;
    const title = isEdit ? 'Edit Customer' : 'Add New Customer';
    const btnText = isEdit ? 'Save Changes' : 'Create Customer';
    
    const modal = document.createElement('div');
    modal.id = 'customer-modal';
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.8); backdrop-filter: blur(5px);
        display: flex; justify-content: center; align-items: center; z-index: 1000;
    `;

    modal.innerHTML = `
        <div style="background: var(--graphite); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.08); width: 100%; max-width: 450px; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.4rem;">${title}</h3>
            <form id="customer-form" style="display: grid; gap: 1rem;">
                <input type="hidden" id="cust-id" value="${isEdit ? customer.user_id : ''}">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <label style="font-size: 0.85rem; color: var(--muted);">First Name
                        <input type="text" id="cust-fname" value="${isEdit ? customer.first_name : ''}" required style="width: 100%; margin-top: 0.3rem; padding: 0.6rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.5); color: #fff;">
                    </label>
                    <label style="font-size: 0.85rem; color: var(--muted);">Last Name
                        <input type="text" id="cust-lname" value="${isEdit ? customer.last_name : ''}" required style="width: 100%; margin-top: 0.3rem; padding: 0.6rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.5); color: #fff;">
                    </label>
                </div>
                <label style="font-size: 0.85rem; color: var(--muted);">Email
                    <input type="email" id="cust-email" value="${isEdit ? customer.email : ''}" required style="width: 100%; margin-top: 0.3rem; padding: 0.6rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.5); color: #fff;">
                </label>
                <label style="font-size: 0.85rem; color: var(--muted);">Phone
                    <input type="text" id="cust-phone" value="${isEdit ? (customer.phone || '') : ''}" style="width: 100%; margin-top: 0.3rem; padding: 0.6rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.5); color: #fff;">
                </label>
                
                ${!isEdit ? '<p style="font-size: 0.8rem; color: var(--accent); margin-top: 0.5rem;">Default password will be set to: <strong>Password123</strong></p>' : ''}

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                    <button type="button" onclick="document.getElementById('customer-modal').remove()" style="padding: 0.7rem 1.2rem; background: transparent; border: none; color: var(--muted); cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 0.7rem 1.2rem; background: var(--accent); color: #000; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">${btnText}</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(modal);

    document.getElementById('customer-form').addEventListener('submit', saveCustomer);
}

async function saveCustomer(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.textContent = 'Saving...';
    btn.disabled = true;

    const payload = {
        user_id: document.getElementById('cust-id').value,
        first_name: document.getElementById('cust-fname').value,
        last_name: document.getElementById('cust-lname').value,
        email: document.getElementById('cust-email').value,
        phone: document.getElementById('cust-phone').value
    };

    try {
        const response = await fetch('api_customers.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();

        if (response.ok && result.success) {
            document.getElementById('customer-modal').remove();
            showAlert(result.message, 'success');
            fetchCustomers();
        } else {
            alert(result.error || 'Failed to save customer.');
            btn.textContent = 'Save';
            btn.disabled = false;
        }
    } catch (err) {
        alert('Network error.');
        btn.textContent = 'Save';
        btn.disabled = false;
    }
}

async function deleteCustomer(userId) {
    if (!confirm('Are you sure you want to delete this customer? This action cannot be undone.')) return;

    try {
        const response = await fetch('api_customers.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        const result = await response.json();

        if (response.ok && result.success) {
            showAlert('Customer deleted successfully.', 'success');
            fetchCustomers();
        } else {
            showAlert(result.error || 'Failed to delete customer.', 'error');
        }
    } catch (err) {
        showAlert('Network error.', 'error');
    }
}

function showAlert(msg, type) {
    const alertBox = document.getElementById('customer-alert');
    if (!alertBox) return;
    alertBox.textContent = msg;
    alertBox.style.display = 'block';
    
    if (type === 'success') {
        alertBox.style.background = 'rgba(81, 207, 102, 0.1)';
        alertBox.style.color = '#51cf66';
        alertBox.style.border = '1px solid rgba(81, 207, 102, 0.2)';
    } else {
        alertBox.style.background = 'rgba(255, 107, 107, 0.1)';
        alertBox.style.color = '#ff6b6b';
        alertBox.style.border = '1px solid rgba(255, 107, 107, 0.2)';
    }

    setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
}