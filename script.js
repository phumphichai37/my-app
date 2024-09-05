document.addEventListener('DOMContentLoaded', function () {
    // Fetch order data from the backend
    fetch('get_orders.php')
        .then(response => response.json())
        .then(orders => {
            const orderList = document.getElementById('order-list');

            orders.forEach(order => {
                const orderCard = document.createElement('div');
                orderCard.classList.add('order-card');

                // Create dropdown for updating status
                const statusOptions = `
                    <select class="form-select mb-2" id="status-select-${order.order_id}">
                        <option value="กำลังจัดเตรียมสินค้า" ${order.status_order === 'กำลังจัดเตรียมสินค้า' ? 'selected' : ''}>กำลังจัดเตรียมสินค้า</option>
                        <option value="กำลังจัดส่ง" ${order.status_order === 'กำลังจัดส่ง' ? 'selected' : ''}>กำลังจัดส่ง</option>
                        <option value="จัดส่งเสร็จสิ้น" ${order.status_order === 'จัดส่งเสร็จสิ้น' ? 'selected' : ''}>จัดส่งเสร็จสิ้น</option>
                    </select>
                `;

                orderCard.innerHTML = `
                    <h2>No: ${order.order_id}</h2>
                    <div class="order-details"><strong>ราคารวมสินค้า:</strong> ${order.total_price} THB</div>
                    <div class="order-details"><strong>สถานะสินค้า:</strong> ${order.status_payment}</div>
                    <div class="order-details"><strong>สถานะการแจ้งเตือน:</strong> ${order.status_noti}</div>
                    <div class="order-details"><strong>เวลาสั่งซื้อสินค้า:</strong> ${order.order_time}</div>
                    <div class="order-details"><strong>สถานะสินค้า:</strong> ${statusOptions}</div>
                    <button class="btn btn-primary" onclick="updateStatus(${order.order_id})">อัปเดตสถานะ</button>
                `;

                orderList.appendChild(orderCard);
            });
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
        });
});

function updateStatus(orderId) {
    const selectedStatus = document.getElementById(`status-select-${orderId}`).value;

    fetch('update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            order_id: orderId,
            status_order: selectedStatus
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('สถานะอัปเดตเรียบร้อยแล้ว');
            } else {
                alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
        });
}
