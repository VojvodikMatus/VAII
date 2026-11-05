// Potvrdenie pred odoslaním formulára s atribútom data-confirm (napr. zmazanie záznamu).
document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !confirm(message)) {
        event.preventDefault();
    }
});