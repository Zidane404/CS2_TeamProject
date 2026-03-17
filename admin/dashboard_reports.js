// dashboard_reports.js

function initOverviewModule() {
    const contentDiv = document.getElementById('admin-content');
    
    // Build initial shell
    contentDiv.innerHTML = `
        <div style="margin-bottom: 2rem;">
            <h2>Dashboard Overview</h2>
            <p style="color: var(--muted);">Real-time inventory metrics and restock priorities.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
            <div style="background: linear-gradient(145deg, var(--graphite), var(--deep-black)); border: 1px solid rgba(255,255,255,0.08); padding: 1.5rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <div style="color: var(--muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Total Inventory Value</div>
                <div id="metric-value" style="font-size: 2.2rem; font-weight: 600; color: var(--accent);">£0.00</div>
            </div>
            
            <div style="background: linear-gradient(145deg, var(--graphite), var(--deep-black)); border: 1px solid rgba(255,255,255,0.08); padding: 1.5rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <div style="color: var(--muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Items Needing Restock</div>
                <div id="metric-low" style="font-size: 2.2rem; font-weight: 600; color: #ff6b6b;">0</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            
            <div>
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem; color: #fff;">🔥 Restock Urgency Dashboard</h3>
                <div style="overflow-x: auto; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08);">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; background: var(--graphite);">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); background: rgba(0,0,0,0.3);">
                                <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem;">PRODUCT</th>
                                <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem;">DEFICIT SCORE</th>
                                <th style="padding: 1rem; color: var(--muted); font-size: 0.85rem; text-align: right;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody id="urgency-table-body">
                            <tr><td colspan="3" style="padding: 1.5rem; text-align: center; color: var(--muted);">Analyzing inventory...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem; color: #fff;">Recent Stock Movement</h3>
                <div id="activity-feed" style="background: var(--graphite); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1rem;">
                    <p style="color: var(--muted); font-size: 0.9rem; text-align: center;">Loading activity...</p>
                </div>
            </div>
        </div>
    `;

    fetchReportData();
}

async function fetchReportData() {
    try {
        const response = await fetch('api_inventory.php?action=reports');
        const data = await response.json();

        if (data.success) {
            // Update Metrics
            document.getElementById('metric-value').textContent = `£${Number(data.total_value).toLocaleString()}`;
            document.getElementById('metric-low').textContent = data.low_stock_count;

            // Render Urgency Table
            const urgencyBody = document.getElementById('urgency-table-body');
            urgencyBody.innerHTML = '';
            
            if (data.urgency_list.length === 0) {
                urgencyBody.innerHTML = `<tr><td colspan="3" style="padding: 1.5rem; text-align: center; color: #51cf66;">All stock levels are healthy.</td></tr>`;
            } else {
                data.urgency_list.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid rgba(255, 255, 255, 0.04)';
                    
                    const deficit = parseInt(item.urgency_score);
                    const suggestedOrder = parseInt(item.threshold_level) * 3;
                    
                    // Create mailto link
                    const subject = encodeURIComponent(`Restock Request: ${item.sku}`);
                    const body = encodeURIComponent(`Hello,\n\nWe urgently need to restock the following item:\n\nProduct: ${item.name}\nSKU: ${item.sku}\nSuggested Quantity: ${suggestedOrder} units\n\nPlease let us know the estimated lead time.\n\nThank you.`);
                    const mailtoUrl = `mailto:supplier@driprodrown.com?subject=${subject}&body=${body}`;

                    tr.innerHTML = `
                        <td style="padding: 1rem;">
                            <div style="font-weight: 500;">${item.name}</div>
                            <div style="font-size: 0.75rem; color: var(--muted);">SKU: ${item.sku}</div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem; background: rgba(255,107,107,0.1); color: #ff6b6b; font-weight: 600;">
                                -${deficit} Units
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <a href="${mailtoUrl}" style="display: inline-block; padding: 0.4rem 0.8rem; background: transparent; border: 1px solid var(--accent); color: var(--accent); border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 500; transition: background 0.2s;">Draft Supplier Email</a>
                        </td>
                    `;
                    urgencyBody.appendChild(tr);
                });
            }

            // Render Activity Feed
            const activityFeed = document.getElementById('activity-feed');
            activityFeed.innerHTML = '';
            
            if (data.recent_activity.length === 0) {
                activityFeed.innerHTML = '<p style="color: var(--muted); font-size: 0.9rem; text-align: center;">No recent activity.</p>';
            } else {
                data.recent_activity.forEach(log => {
                    const isIncoming = log.change_type === 'incoming';
                    const color = isIncoming ? '#51cf66' : '#ff6b6b';
                    const sign = isIncoming ? '+' : '';
                    const date = new Date(log.created_at).toLocaleDateString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

                    activityFeed.innerHTML += `
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0.8rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div>
                                <div style="font-size: 0.85rem; font-weight: 500; color: #fff;">${log.name}</div>
                                <div style="font-size: 0.75rem; color: var(--muted);">${date}</div>
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: ${color};">
                                ${sign}${log.quantity_changed}
                            </div>
                        </div>
                    `;
                });
            }
        }
    } catch (err) {
        console.error("Failed to load reports", err);
    }
}