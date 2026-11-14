import { debounce } from '../debounce';

export function debouncedSearch(delay = 350, resultsId = null) {
    let form;
    let searchInput;
    let errorMessage;
    let displayedQuery;
    let scheduleSearch;
    let pendingRequest;

    return {
        init() {
            form = this.$el;
            searchInput = form.querySelector('[data-debounce-search]');
            errorMessage = form.querySelector('[data-search-error]');
            displayedQuery = searchInput.value.trim();
            scheduleSearch = debounce(() => {
                if (searchInput.value.trim() !== displayedQuery) {
                    form.requestSubmit();
                }
            }, delay);
        },

        onSearchInput(event) {
            if (!resultsId || event.target !== searchInput) return;

            scheduleSearch.cancel();
            pendingRequest?.abort();
            errorMessage.hidden = true;

            if (!event.isComposing) {
                scheduleSearch();
            }
        },

        onFilterChange(event) {
            if (event.target.matches('[data-search-filter]')) {
                form.requestSubmit();
            }
        },

        async onSubmit(event) {
            scheduleSearch.cancel();
            const results = resultsId && document.getElementById(resultsId);
            if (!results) return;

            event.preventDefault();
            errorMessage.hidden = true;
            pendingRequest?.abort();
            const request = new AbortController();
            pendingRequest = request;

            const url = new URL(form.action);
            url.search = new URLSearchParams(new FormData(form)).toString();
            const query = searchInput.value.trim();

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'text/html' },
                    signal: request.signal,
                });
                if (request.signal.aborted) return;
                if (response.redirected && new URL(response.url).pathname !== url.pathname) {
                    window.location.assign(response.url);
                    return;
                }
                if (!response.ok) throw new Error('Search request failed');

                const html = await response.text();
                if (request.signal.aborted) return;

                const page = new DOMParser().parseFromString(html, 'text/html');
                const updatedResults = page.getElementById(resultsId);
                if (!updatedResults) throw new Error('Missing search results');

                results.replaceChildren(...updatedResults.childNodes);
                form.closest('main').querySelectorAll('[data-search-sync][id]').forEach(element => {
                    const updatedElement = page.getElementById(element.id);
                    if (updatedElement) element.replaceWith(updatedElement);
                });
                window.history.replaceState(window.history.state, '', url);
                displayedQuery = query;
            } catch {
                if (request.signal.aborted) return;

                errorMessage.textContent = 'Hasil pencarian gagal diperbarui. Tekan Enter untuk mencoba lagi.';
                errorMessage.hidden = false;
            } finally {
                if (pendingRequest === request) pendingRequest = null;
            }
        },

        destroy() {
            scheduleSearch.cancel();
            pendingRequest?.abort();
        },
    };
}
