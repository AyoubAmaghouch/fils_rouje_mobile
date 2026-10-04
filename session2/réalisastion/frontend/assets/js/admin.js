/**
 * Admin Panel — JavaScript
 * Gestion des confirmations, alertes, sidebar mobile, preview d'image
 */

// =========================================================
// 1. CONFIRMATION DE SUPPRESSION
// =========================================================

/**
 * Affiche une modale de confirmation stylisée avant suppression.
 * @param {string} url         - URL de suppression (ex: supprimer.php?id=5)
 * @param {string} nom         - Nom de l'élément à supprimer
 * @param {string} [type='cet élément'] - Type de l'élément
 */
function confirmerSuppression(url, nom, type = 'cet élément') {
    // Créer l'overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.id = 'confirm-modal';

    overlay.innerHTML = `
        <div class="modal-box">
            <div class="modal-icon">🗑️</div>
            <h3>Confirmer la suppression</h3>
            <p>Voulez-vous vraiment supprimer <strong>${type}</strong> :<br>
               <strong style="color: var(--primary-light);">${escapeHtml(nom)}</strong> ?<br>
               <small style="color:var(--danger);margin-top:6px;display:block;">⚠️ Cette action est irréversible.</small>
            </p>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="fermerModal()">
                    ✕ Annuler
                </button>
                <a href="${url}" class="btn btn-danger">
                    🗑️ Supprimer
                </a>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Fermer en cliquant sur l'overlay
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) fermerModal();
    });

    // Fermer avec Échap
    document.addEventListener('keydown', function handleEsc(e) {
        if (e.key === 'Escape') {
            fermerModal();
            document.removeEventListener('keydown', handleEsc);
        }
    });
}

function fermerModal() {
    const modal = document.getElementById('confirm-modal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.2s ease forwards';
        setTimeout(() => modal.remove(), 200);
    }
}

// =========================================================
// 2. AUTO-FERMETURE DES ALERTES
// =========================================================

function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Bouton de fermeture manuel
        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => dismissAlert(alert));
        }

        // Auto-fermeture après 5 secondes
        setTimeout(() => dismissAlert(alert), 5000);
    });
}

function dismissAlert(alert) {
    alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease, margin 0.4s ease, padding 0.4s ease';
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-8px)';
    alert.style.maxHeight = '0';
    alert.style.margin = '0';
    alert.style.padding = '0';
    alert.style.overflow = 'hidden';
    setTimeout(() => alert.remove(), 400);
}

// =========================================================
// 3. SIDEBAR MOBILE
// =========================================================

function initSidebar() {
    const sidebar  = document.querySelector('.sidebar');
    const overlay  = document.querySelector('.sidebar-overlay');
    const hamburger = document.querySelector('.hamburger');

    if (!sidebar || !overlay || !hamburger) return;

    hamburger.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    });
}

// =========================================================
// 4. PREVIEW D'IMAGE
// =========================================================

/**
 * Initialise les previews d'image sur les inputs file.
 * Usage HTML : <input type="file" data-preview="preview-id" accept="image/*">
 */
function initImagePreviews() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');

    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            const previewEl = document.getElementById(previewId);

            if (!previewEl || !this.files || !this.files[0]) return;

            const file = this.files[0];

            // Vérification côté client (type MIME)
            if (!file.type.startsWith('image/')) {
                showToast('⚠️ Le fichier sélectionné n\'est pas une image.', 'warning');
                this.value = '';
                return;
            }

            // Vérification taille (max 5 MB)
            if (file.size > 5 * 1024 * 1024) {
                showToast('⚠️ L\'image ne doit pas dépasser 5 MB.', 'warning');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewEl.innerHTML = `<img src="${e.target.result}" alt="Aperçu">`;
            };
            reader.readAsDataURL(file);
        });
    });
}

// =========================================================
// 5. TOAST NOTIFICATIONS
// =========================================================

function showToast(message, type = 'info') {
    const colors = {
        success: '#10b981',
        danger:  '#ef4444',
        warning: '#f59e0b',
        info:    '#6366f1'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--bg-card);
        border: 1px solid ${colors[type] || colors.info};
        border-left: 4px solid ${colors[type] || colors.info};
        color: var(--text-primary);
        padding: 14px 18px;
        border-radius: 10px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 500;
        box-shadow: 0 8px 30px rgba(0,0,0,0.4);
        z-index: 9999;
        max-width: 340px;
        animation: slideInRight 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// =========================================================
// 6. UTILITAIRES
// =========================================================

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}

// Animation CSS manquante pour le toast
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(20px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to   { opacity: 0; transform: translateX(20px); }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to   { opacity: 0; }
    }
`;
document.head.appendChild(toastStyles);

// =========================================================
// 7. INIT AU CHARGEMENT
// =========================================================

document.addEventListener('DOMContentLoaded', function() {
    initAlerts();
    initSidebar();
    initImagePreviews();
});
