// Effet scroll sur le titre
window.addEventListener('scroll', function () {
    const title = document.querySelector('.title');
    if (!title) return; // sécurité

    const titlePosition = title.getBoundingClientRect().top;
    const screenPosition = window.innerHeight / 1.3;

    if (titlePosition < screenPosition) {
        title.classList.add('scrolled');
    } else {
        title.classList.remove('scrolled');
    }
});

// Navigation AJAX entre les pages
document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('.page-link');
    if (links.length > 0) {
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const pageId = this.dataset.id;

                fetch('get_contenu.php?page_id=' + pageId)
                    .then(response => response.text())
                    .then(html => {
                        const presentationDiv = document.querySelector('.presentation-container');
                        if (presentationDiv) {
                            presentationDiv.innerHTML = html;
                            presentationDiv.scrollIntoView({ behavior: 'smooth' });
                        }
                    })
                    .catch(error => console.error('Erreur AJAX :', error));
            });
        });
    }
});

// Boutons d’expansion/repli
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.toggle-button');
    if (buttons.length > 0) {
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const parent = button.parentElement;
                parent.classList.toggle('expanded');
                button.textContent = parent.classList.contains('expanded') ? '▼' : '▶';
            });
        });
    }
});

// Formulaire d’ajout de contenu
document.addEventListener('DOMContentLoaded', () => {
    const contentForm = document.getElementById('contentForm');
    if (contentForm) {
        contentForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(e.target);

            fetch('ajouter_contenu.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error("Erreur lors de l'ajout du contenu.");
                    return response.text();
                })
                .then(() => {
                    alert("Contenu ajouté !");
                    window.location.reload();
                })
                .catch(error => {
                    console.error("Erreur :", error);
                    alert("Échec de l'ajout du contenu.");
                });
        });
    }
});
