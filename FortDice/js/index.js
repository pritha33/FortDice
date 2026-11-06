// Simple landing page - no Firebase needed
console.log('🏦 FortDice · Secure Banking');
document.addEventListener('DOMContentLoaded', () => {
    // Animation for feature cards
    const cards = document.querySelectorAll('.feature-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateX(0)';
        }, 200 + index * 150);
    });
});