// Registrasi Chart.js terpusat (tree-shaking komponen yang dipakai saja).
// Chart.js dipasang lewat npm dan dibundel Vite (tanpa CDN).
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    Filler,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    Filler,
    Tooltip,
    Legend,
);

export { Chart };
export default Chart;
