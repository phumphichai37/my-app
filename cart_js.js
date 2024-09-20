document.addEventListener('DOMContentLoaded', function () {
    const transferButton = document.getElementById('payment_transfer');  // แก้ไขจาก 'transferButton' เป็น 'payment_transfer'
    const cashButton = document.getElementById('payment_cash');          // แก้ไขจาก 'cashButton' เป็น 'payment_cash'
    const paymentImage = document.getElementById('paymentImage');

    // เมื่อกดปุ่มโอนเงิน ให้แสดงรูปภาพ
    transferButton.addEventListener('click', function () {
        paymentImage.style.display = 'block';  // แสดงรูปภาพ
    });

    // เมื่อกดปุ่มเงินสด ให้ซ่อนรูปภาพ
    cashButton.addEventListener('click', function () {
        paymentImage.style.display = 'none';  // ซ่อนรูปภาพ
    });
});
