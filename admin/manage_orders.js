// manage_orders.js

function initOrderModule() {
    const contentDiv = document.getElementById('admin-content');
    
    // Build the shell for the orders module
    contentDiv.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Order Processing</h2>
        </div>
        
        <div id="order-toast" style="display: none; position: fixed; bottom: 20px; right: 20px; padding: 1rem 1.5rem; background: var(--graphite); border: 1px solid var(--accent); border-left: 4px solid var(--accent); color: #fff; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 9999; transition: opacity 0.3s ease;"></div>

        <div style="overflow-x: auto; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; background: var(--graphite);">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); background: rgba(0,0,0,0.3);">
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Order ID</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Customer</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Date</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Total</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Payment</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Status</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase; text-align: right;">Details</th>
                    </tr>
                </thead>
                <tbody id="order-table-body">
                    <tr><td colspan="7" style="padding: 2rem; text-align: center; color: var(--muted);">Loading orders...</td></tr>
                </tbody>
            </table>
        </div>
    `;

    fetchOrders();
}

async function fetchOrders() {
    try {
        const response = await fetch('api_orders.php');
        const data = await response.json();
        const tbody = document.getElementById('order-table-body');

        if (data.success) {
            tbody.innerHTML = '';
            if (data.orders.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="padding: 2rem; text-align: center; color: var(--muted);">No orders found.</td></tr>`;
                return;
            }

            const statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

            data.orders.forEach(order => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid rgba(255, 255, 255, 0.04)';
                
                const placedDate = new Date(order.placed_at).toLocaleString();
                const customerName = order.first_name ? `${order.first_name} ${order.last_name}` : 'Guest';
                
                // Build the dropdown
                let statusOptions = statuses.map(s => 
                    `<option value="${s}" ${order.order_status === s ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
                ).join('');

                tr.innerHTML = `
                    <td style="padding: 1rem; font-weight: 500; color: var(--accent);">#${order.order_id}</td>
                    <td style="padding: 1rem;">
                        <div>${customerName}</div>
                        <div style="font-size: 0.75rem; color: var(--muted);">${order.email || ''}</div>
                    </td>
                    <td style="padding: 1rem; color: var(--muted); font-size: 0.9rem;">${placedDate}</td>
                    <td style="padding: 1rem; font-weight: 500;">£${order.order_total}</td>
                    <td style="padding: 1rem;">
                        <span style="padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem; background: ${order.payment_status === 'paid' ? 'rgba(81,207,102,0.1)' : 'rgba(255,107,107,0.1)'}; color: ${order.payment_status === 'paid' ? '#51cf66' : '#ff6b6b'};">
                            ${order.payment_status.toUpperCase()}
                        </span>
                    </td>
                    <td style="padding: 1rem;">
                        <select onchange="updateOrderStatus(${order.order_id}, this.value)" style="background: rgba(0,0,0,0.5); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 0.4rem; border-radius: 6px; outline: none;">
                            ${statusOptions}
                        </select>
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <button onclick="toggleOrderDetails(${order.order_id}, this.closest('tr'))" style="padding: 0.4rem 0.8rem; background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">View Details</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (err) {
        showOrderToast('Failed to load orders.', true);
    }
}

async function updateOrderStatus(orderId, newStatus) {
    try {
        const response = await fetch('api_orders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, order_status: newStatus })
        });
        const result = await response.json();

        if (response.ok && result.success) {
            showOrderToast(result.message);
        } else {
            showOrderToast(result.error || 'Failed to update status.', true);
        }
    } catch (err) {
        showOrderToast('Network error while updating.', true);
    }
}

async function toggleOrderDetails(orderId, parentRow) {
    // Check if details row already exists right after this row
    const nextRow = parentRow.nextElementSibling;
    if (nextRow && nextRow.classList.contains('details-row')) {
        nextRow.remove(); // Toggle off
        return;
    }

    try {
        const response = await fetch(`api_orders.php?order_id=${orderId}`);
        const data = await response.json();

        if (data.success) {
            const detailsRow = document.createElement('tr');
            detailsRow.className = 'details-row';
            
            let itemsHTML = data.items.map(item => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px dashed rgba(255,255,255,0.1);">
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div style="width: 40px; height: 40px; border-radius: 4px; background: rgba(0,0,0,0.5); overflow: hidden;">
                            ${item.main_image ? `<img src="../${item.main_image}" style="width: 100%; height: 100%; object-fit: cover;">` : ''}
                        </div>
                        <div>
                            <div style="font-size: 0.9rem;">${item.name}</div>
                            <div style="font-size: 0.75rem; color: var(--muted);">SKU: ${item.sku}</div>
                        </div>
                    </div>
                    <div style="text-align: right; font-size: 0.9rem;">
                        ${item.quantity} x £${item.price_each} = <strong>£${item.total_price}</strong>
                    </div>
                </div>
            `).join('');

            if (data.items.length === 0) itemsHTML = '<p style="color: var(--muted);">No items found for this order.</p>';

            detailsRow.innerHTML = `
                <td colspan="7" style="padding: 0; background: rgba(0,0,0,0.2);">
                    <div style="padding: 1.5rem 2rem; border-left: 3px solid var(--accent);">
                        <h4 style="margin-bottom: 1rem; font-size: 0.95rem; color: var(--muted); text-transform: uppercase;">Order Items</h4>
                        ${itemsHTML}
                    </div>
                </td>
            `;
            
            // Insert the details row right after the parent row
            parentRow.parentNode.insertBefore(detailsRow, parentRow.nextSibling);
        }
    } catch (err) {
        showOrderToast('Failed to load order items.', true);
    }
}

function showOrderToast(msg, isError = false) {
    const toast = document.getElementById('order-toast');
    if (!toast) return;
    
    toast.textContent = msg;
    toast.style.borderLeftColor = isError ? '#ff6b6b' : 'var(--accent)';
    toast.style.borderColor = isError ? '#ff6b6b' : 'var(--accent)';
    toast.style.display = 'block';
    toast.style.opacity = '1';

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 3000);
}