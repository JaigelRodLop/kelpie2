document.addEventListener("DOMContentLoaded", () => {
    const logoutBtn = document.querySelector(".btn-danger");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", (e) => {
            if (!confirm("¿Seguro que deseas cerrar sesión?")) {
                e.preventDefault();
            }
        });
    }

    const ticketForm = document.querySelector("form[action='']");
    if (ticketForm) {
        ticketForm.addEventListener("submit", () => {
            alert("Tu ticket ha sido enviado correctamente.");
        });
    }
});