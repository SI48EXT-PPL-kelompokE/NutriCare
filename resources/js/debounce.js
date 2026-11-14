export function debounce(callback, delay) {
    let timer;

    function debounced(...args) {
        debounced.cancel();
        timer = setTimeout(() => callback.apply(this, args), delay);
    }

    debounced.cancel = () => clearTimeout(timer);

    return debounced;
}
