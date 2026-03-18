// manage_inventory.js

let inventoryData = [];

function initInventoryModule() {
    const contentDiv = document.getElementById('admin-content');
    
    contentDiv.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <h2>Inventory Management</h2>
            <div style="display: flex; gap: 1rem; flex-grow: 1; max-width: 500px; justify-content: flex-end;">
                <input type="text" id="inventory-search" placeholder="Search by name or SKU..." style="width: 100%; max-width: 250px; padding: 0.6rem 1rem; border-radius: 999px; border: 1px solid rgba(255,255,255,0.14); background: rgba(5,5,8,0.9); color: #fff; outline: none;">
                <button onclick="alert('Product modal creation coming soon!')" class="btn" style="background: var(--accent); color: #151515; border: none; font-weight: 600;">+ Add Product</button>
            </div>
        </div>
        
        <div id="inv-toast" style="display: none; position: fixed; bottom: 20px; right: 20px; padding: 1rem 1.5rem; background: var(--graphite); border-left: 4px solid var(--accent); color: #fff; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 9999; transition: opacity 0.3s ease;"></div>

        <div style="overflow-x: auto; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; background: var(--graphite);">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); background: rgba(0,0,0,0.3);">
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Product</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Price</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Stock</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Status</th>
                        <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventory-table-body">
                    <tr><td colspan="5" style="padding: 2rem; text-align: center; color: var(--muted);">Loading inventory...</td></tr>
                </tbody>
            </table>
        </div>
    `;

    // Attach Search Listener
    document.getElementById('inventory-search').addEventListener('input', (e) => {
        renderInventoryTable(e.target.value);
    });

    fetchInventory();
}

async function fetchInventory() {
    try {
        const response = await fetch('api_inventory.php');
        const data = await response.json();
        
        if (data.success) {
            inventoryData = data.products;
            renderInventoryTable();
        } else {
            showInvToast('Failed to load inventory.', true);
        }
    } catch (err) {
        showInvToast('Network error while fetching inventory.', true);
    }
}

function renderInventoryTable(searchQuery = '') {
    const tbody = document.getElementById('inventory-table-body');
    tbody.innerHTML = '';

    const lowerQuery = searchQuery.toLowerCase();
    const filteredData = inventoryData.filter(p => 
        (p.name && p.name.toLowerCase().includes(lowerQuery)) || 
        (p.sku && p.sku.toLowerCase().includes(lowerQuery))
    );

    if (filteredData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="padding: 2rem; text-align: center; color: var(--muted);">No products found.</td></tr>`;
        return;
    }

    filteredData.forEach(p => {
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid rgba(255, 255, 255, 0.04)';
        
        const stockQty = parseInt(p.stock_quantity) || 0;
        const threshold = parseInt(p.threshold_level) || 5;
        
        // Stock Alert Logic
        let badgeHTML = '';
        if (stockQty <= 0) {
            badgeHTML = `<span style="padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; background: rgba(255,107,107,0.1); color: #ff6b6b; font-weight: 600;">OUT OF STOCK</span>`;
        } else if (stockQty <= threshold) {
            badgeHTML = `<span style="padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; background: rgba(252,196,25,0.1); color: #fcc419; font-weight: 600;">LOW STOCK</span>`;
        } else {
            badgeHTML = `<span style="padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; background: rgba(81,207,102,0.1); color: #51cf66; font-weight: 600;">IN STOCK</span>`;
        }

        const imageSrc = p.main_image ? `../${p.main_image}` : '../images/diamond-cross.jpg';

        tr.innerHTML = `
            <td style="padding: 1rem;">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <img src="${imageSrc}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                    <div>
                        <div style="font-weight: 500; font-size: 0.95rem;">${p.name}</div>
                        <div style="font-size: 0.75rem; color: var(--muted); margin-bottom: 0.2rem;">SKU: ${p.sku}</div>
                        <div style="font-size: 0.75rem; color: var(--muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${p.short_description || ''}</div>
                    </div>
                </div>
            </td>
            <td style="padding: 1rem; font-weight: 500;">£${p.price}</td>
            <td style="padding: 1rem; font-size: 1.1rem; font-weight: 600;">${stockQty}</td>
            <td style="padding: 1rem;">${badgeHTML}</td>
            <td style="padding: 1rem; text-align: right;">
                <button onclick="promptStockAdjustment(${p.product_id}, '${p.name.replace(/'/g, "\\'")}')" style="padding: 0.5rem 1rem; background: transparent; border: 1px solid var(--accent); color: var(--accent); border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: background 0.2s;">Adjust Stock</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function promptStockAdjustment(productId, productName) {
    const input = prompt(`Adjust stock for: ${productName}\n\nEnter a positive number for incoming stock (e.g., 10)\nEnter a negative number for shrinkage or outgoing (e.g., -3)`);
    
    if (input === null || input.trim() === '') return; // User cancelled
    
    const qtyChange = parseInt(input, 10);
    if (isNaN(qtyChange) || qtyChange === 0) {
        alert("Please enter a valid non-zero integer.");
        return;
    }

    try {
        const response = await fetch('api_inventory.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_stock',
                product_id: productId,
                quantity_changed: qtyChange
            })
        });
        const result = await response.json();

        if (response.ok && result.success) {
            showInvToast(`Stock updated successfully by ${qtyChange}`);
            fetchInventory(); // Refresh table
        } else {
            showInvToast(result.error || 'Failed to update stock.', true);
        }
    } catch (err) {
        showInvToast('Network error while updating stock.', true);
    }
}

function showInvToast(msg, isError = false) {
    const toast = document.getElementById('inv-toast');
    if (!toast) return;
    
    toast.textContent = msg;
    toast.style.borderLeftColor = isError ? '#ff6b6b' : 'var(--accent)';
    toast.style.display = 'block';
    toast.style.opacity = '1';

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 3000);
}