document.getElementById("updateByIdForm").addEventListener("submit", function(event) {
    event.preventDefault();
    let productId = document.getElementById("productId").value;
    console.log("Product ID:", productId); // Выводим значение в консоль для проверки
    if (productId.trim() !== '') {
        updateProductById(productId);
    } else {
        alert("Please enter a valid product ID.");
    }
});

document.getElementById("updateAllBtn").addEventListener("click", function() {
    updateAllProducts();
});

function updateProductById(productId) {
    fetch('/php/update_products.php', {
        method: 'POST',
        body: new URLSearchParams({
            'productId': productId
        })
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById("updateResult").innerText = data;
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateAllProducts() {
    fetch('/php/update_products.php', {
        method: 'POST',
        body: new URLSearchParams({
            'updateAll': true
        })
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById("updateResult").innerText = data;
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateProductsByVendor() {
    let vendorId = document.getElementById("vendorId").value;
    fetch('/php/update_products.php', {
        method: 'POST', 
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'vendorId': vendorId
        })
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById("updateResult").innerText = data;
    })
    .catch(error => {
        console.error('Error:', error);
    });
}