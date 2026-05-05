document.addEventListener("DOMContentLoaded", function () {

    const lightbox = document.getElementById("lightbox");
    const lightboxImg = document.getElementById("lightbox-img");
    const closeBtn = document.querySelector(".lightbox-close");

    // pega todas as imagens da gallery
    document.querySelectorAll(".gallery-item img").forEach(img => {
        img.addEventListener("click", function () {
            lightbox.style.display = "flex";
            lightboxImg.src = this.src;
        });
    });

    // fechar ao clicar no X
    closeBtn.addEventListener("click", function () {
        lightbox.style.display = "none";
    });

    // fechar ao clicar fora da imagem
    lightbox.addEventListener("click", function (e) {
        if (e.target === lightbox) {
            lightbox.style.display = "none";
        }
    });

});

function openEdit(id, username, email, role) {

    document.getElementById('editUserSection').style.display = 'block';

    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;

    // optional: scroll to form
    document.getElementById('editUserSection')
        .scrollIntoView({ behavior: 'smooth' });
}

function toggleStaffFields() {
    const role = document.getElementById('roleSelect').value;
    document.querySelector('.staff-fields').style.display =
        (role === 'Staff') ? 'block' : 'none';
}