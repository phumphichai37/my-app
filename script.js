document.addEventListener('DOMContentLoaded', function () {
    fetch(`get_orders.php?timestamp=${new Date().getTime()}`)
        .then(response => response.json())
        .then(orders => {
            const orderList = document.getElementById('order-list');

            // Counters for different statuses
            let pendingCount = 0;
            let preparingCount = 0;
            let shippingCount = 0;
            let completedCount = 0;

            orders.forEach(order => {
                const orderCard = document.createElement('div');
                orderCard.classList.add('order-card');

                // Count orders by their status
                switch (order.status_payment) {
                    case 'รอการอนุมัติ':
                        pendingCount++;
                        break;
                    case 'กำลังจัดเตรียมสินค้า':
                        preparingCount++;
                        break;
                    case 'กำลังจัดส่ง':
                        shippingCount++;
                        break;
                    case 'จัดส่งเสร็จสิ้น':
                        completedCount++;
                        break;
                }

                // Create dropdown for updating status
                const statusOptions = `
                    <select class="form-select mb-2" id="status-select-${order.order_id}">
                        <option value="รอการอนุมัติ" ${order.status_payment === 'รอการอนุมัติ' ? 'selected' : ''}>รอการอนุมัติ</option>
                        <option value="กำลังจัดเตรียมสินค้า" ${order.status_payment === 'กำลังจัดเตรียมสินค้า' ? 'selected' : ''}>กำลังจัดเตรียมสินค้า</option>
                        <option value="กำลังจัดส่ง" ${order.status_payment === 'กำลังจัดส่ง' ? 'selected' : ''}>กำลังจัดส่ง</option>
                        <option value="จัดส่งเสร็จสิ้น" ${order.status_payment === 'จัดส่งเสร็จสิ้น' ? 'selected' : ''}>จัดส่งเสร็จสิ้น</option>
                    </select>
                `;

                // Display order details and status dropdown
                orderCard.innerHTML = `
                    <h2>Order No: ${order.order_id}</h2>
                    <div class="order-details"><strong>ราคารวมสินค้า:</strong> ${order.total_price} THB</div>
                    <div class="order-details"><strong>สถานะการชำระเงิน:</strong> ${order.payment_info}</div>
                    <div class="order-details"><strong>สถานะการแจ้งเตือน:</strong> ${order.status_noti}</div>
                    <div class="order-details"><strong>เวลาสั่งซื้อสินค้า:</strong> ${order.order_time}</div>
                    <div class="order-details"><strong>สถานะสินค้า:</strong> ${statusOptions}</div>
                    <button class="btn btn-primary" onclick="updateStatus(${order.order_id})">อัปเดตสถานะ</button>
                `;

                orderList.appendChild(orderCard);
            });

            // Update the counters in the HTML
            document.getElementById('pending-count').innerText = `${pendingCount} ออเดอร์`;
            document.getElementById('preparing-count').innerText = `${preparingCount} ออเดอร์`;
            document.getElementById('shipping-count').innerText = `${shippingCount} ออเดอร์`;
            document.getElementById('completed-count').innerText = `${completedCount} ออเดอร์`;
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
        });
});

// Function for updating order status
function updateStatus(orderId) {
    const submittedOrders = JSON.parse(localStorage.getItem('submittedOrders')) || [];

    // Check if the order has already been submitted
    if (submittedOrders.includes(orderId)) {
        alert('คำสั่งนี้ถูกส่งแล้ว');
        return;
    }

    const selectedStatus = document.getElementById(`status-select-${orderId}`).value;

    const button = document.querySelector(`button[onclick="updateStatus(${orderId})"]`);
    button.disabled = true; // Disable the button to avoid duplicate clicks

    fetch('update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            order_id: orderId,
            status_payment: selectedStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('สถานะอัปเดตเรียบร้อยแล้ว');
            // Mark order as submitted in localStorage
            submittedOrders.push(orderId);
            localStorage.setItem('submittedOrders', JSON.stringify(submittedOrders));
        } else {
            alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
        }
        button.disabled = false; // Re-enable button after completion
    })
    .catch(error => {
        console.error('Error updating status:', error);
        button.disabled = false; // Re-enable if there is an error
    });
}
