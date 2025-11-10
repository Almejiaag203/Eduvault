// Define the expected footer HTML
const expectedFooterHTML = `
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; ${new Date().getFullYear()} <a href="https://www.facebook.com/TechFusionData" target="_blank" rel="noopener noreferrer">TechFusion Data</a></span>
            </div>
        </div>
    </footer>
`;

// Function to enforce the footer
function enforceFooter() {
    // Find the footer element
    let footer = document.querySelector('footer.sticky-footer.bg-white');

    // If footer is missing or content doesn't match, restore it
    if (!footer || footer.outerHTML.trim() !== expectedFooterHTML.trim()) {
        // If footer exists but is incorrect, remove it
        if (footer) {
            footer.remove();
        }

        // Create a new footer element
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = expectedFooterHTML;
        const newFooter = tempDiv.firstElementChild;

        // Append the footer to the body
        document.body.appendChild(newFooter);
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', enforceFooter);

// Run periodically to ensure footer persists (every 5 seconds)
setInterval(enforceFooter, 5000);

// Prevent modifications to the footer via MutationObserver
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.target.matches('footer.sticky-footer.bg-white') || mutation.removedNodes.length > 0) {
            enforceFooter();
        }
    });
});

// Observe changes to the body and its subtree
observer.observe(document.body, { childList: true, subtree: true });