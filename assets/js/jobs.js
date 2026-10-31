
document.addEventListener('DOMContentLoaded', () => {

    const results = document.getElementById('jobResults');
    const search = document.getElementById('jobSearch');
    const category = document.getElementById('categoryFilter');
    const type = document.getElementById('typeFilter');

    // Stop if this page does not contain the job search elements
    if (!results || !search || !category || !type) {
        return;
    }

    let timer;

    // Load and display matching jobs
    async function loadJobs() {

        const params = new URLSearchParams({
            q: search.value,
            category: category.value,
            type: type.value
        });

        results.innerHTML = '<div class="loading">Loading opportunities...</div>';

        try {

            const response = await fetch(
                `${window.SKILLBRIDGE_BASE}/ajax/search_jobs.php?${params}`
            );

            if (!response.ok) {
                throw new Error('Request failed');
            }

            results.innerHTML = await response.text();

        } catch (error) {

            results.innerHTML = `
                <div class="empty-state">
                    Could not load jobs. Please refresh and try again.
                </div>
            `;

        }
    }

    // Live search with a short delay
    search.addEventListener('input', () => {

        clearTimeout(timer);

        timer = setTimeout(loadJobs, 250);

    });

    // Category filter
    category.addEventListener('change', loadJobs);

    // Job type filter
    type.addEventListener('change', loadJobs);

    // Initial job loading
    loadJobs();

});
