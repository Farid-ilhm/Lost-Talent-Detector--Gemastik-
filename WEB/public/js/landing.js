let lastScrollY = window.scrollY;
const navbar = document.querySelector('.landing-navbar');

if (navbar) {
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY <= 50) {
            navbar.classList.remove('nav-hidden');
            lastScrollY = currentScrollY;
            return;
        }

        if (currentScrollY > lastScrollY && currentScrollY > 80) {
            navbar.classList.add('nav-hidden');
        } else if (currentScrollY < lastScrollY) {
            navbar.classList.remove('nav-hidden');
        }
        
        lastScrollY = currentScrollY;
    }, { passive: true });
}

function handleContactSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnHtml = submitBtn.innerHTML;
    
    // Set loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> MENGIRIM...';
    
    fetch('/contact', {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        body: new FormData(form)
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (status === 200 && data.success) {
            showToast('Pesan terkirim! CS kami akan membalas dalam 1x24 jam.', 'success');
            form.reset();
        } else {
            const errorMsg = data.message || 'Gagal mengirim pesan. Silakan coba lagi.';
            showToast(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan koneksi.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.position = 'fixed';
    toast.style.bottom = '24px';
    toast.style.right = '24px';
    toast.style.backgroundColor = type === 'success' ? '#1C1917' : '#DC2626';
    toast.style.color = '#FFFFFF';
    toast.style.padding = '16px 24px';
    toast.style.borderRadius = '12px';
    toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
    toast.style.zIndex = '9999';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '12px';
    toast.style.fontSize = '0.95rem';
    toast.style.fontWeight = '600';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    toast.style.transition = 'all 0.3s ease';
    
    const icon = type === 'success' 
        ? '<i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 1.2rem;"></i>'
        : '<i class="fa-solid fa-circle-xmark" style="color: #FFFFFF; font-size: 1.2rem;"></i>';
        
    toast.innerHTML = `${icon} <span>${message}</span>`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 4000);
}
