document.addEventListener('DOMContentLoaded', function() {
    const liveSearch = document.getElementById('liveSearch');
    const productCards = document.querySelectorAll('.product-card');

    liveSearch.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        
        productCards.forEach(card => {
            const productName = card.dataset.name;
            const isVisible = productName.includes(searchTerm);
            
            card.style.opacity = isVisible ? '1' : '0';
            card.style.transform = isVisible ? 'scale(1)' : 'scale(0.9)';
            card.style.pointerEvents = isVisible ? 'all' : 'none';
            card.style.position = isVisible ? 'static' : 'absolute';
        });
    });
});
function debounce(func, timeout = 300){
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
}

liveSearch.addEventListener('input', debounce(function(e) {
    // Le code de filtrage ici
}));