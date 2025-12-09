function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).style.display = "block";
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

function toggleDetails(id) {
    const element = document.getElementById(id);
    element.style.display = (element.style.display === 'block') ? 'none' : 'block';
}

function confirmDelete() {
    return confirm('Você tem certeza que deseja excluir este item?');
}

// --- Funções de Modal ---
const emailModal = document.getElementById('emailModal');
const subscriptionEmailModal = document.getElementById('subscriptionEmailModal');
const paymentEmailModal = document.getElementById('paymentEmailModal');

function openEmailModal(userId) {
    if (emailModal) {
        document.getElementById('modalUserId').value = userId;
        emailModal.style.display = 'block';
    }
}

function closeEmailModal() {
    if (emailModal) {
        emailModal.style.display = 'none';
    }
}

function openSubscriptionEmailModal(subscriptionId) {
    if (subscriptionEmailModal) {
        document.getElementById('modalSubscriptionId').value = subscriptionId;
        subscriptionEmailModal.style.display = 'block';
    }
}

function closeSubscriptionEmailModal() {
    if (subscriptionEmailModal) {
        subscriptionEmailModal.style.display = 'none';
    }
}

function openPaymentEmailModal(paymentId) {
    if (paymentEmailModal) {
        document.getElementById('modalPaymentId').value = paymentId;
        paymentEmailModal.style.display = 'block';
    }
}

function closePaymentEmailModal() {
    if (paymentEmailModal) {
        paymentEmailModal.style.display = 'none';
    }
}

// Evento de inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Abrir a primeira aba por padrão
    const firstTab = document.querySelector('.tab-link');
    if (firstTab) {
        firstTab.click();
    }

    // Fechar modais ao clicar fora
    window.onclick = function(event) {
        if (event.target == emailModal) closeEmailModal();
        if (event.target == subscriptionEmailModal) closeSubscriptionEmailModal();
        if (event.target == paymentEmailModal) closePaymentEmailModal();
    }

    // Adicionar listeners para os botões de fechar
    const closeButtons = document.querySelectorAll('.close-button');
    closeButtons.forEach(button => {
        if (button.closest('#emailModal')) {
            button.onclick = closeEmailModal;
        } else if (button.closest('#subscriptionEmailModal')) {
            button.onclick = closeSubscriptionEmailModal;
        } else if (button.closest('#paymentEmailModal')) {
            button.onclick = closePaymentEmailModal;
        }
    });
});
