<<<<<<< ours
// /assets/app.js

// import the main style sheet
import './styles/main.scss';

// import bootstrap js
import 'bootstrap';

// fontawesome
import '@fortawesome/fontawesome-free/js/fontawesome'
import '@fortawesome/fontawesome-free/js/solid'
import '@fortawesome/fontawesome-free/js/regular'
import '@fortawesome/fontawesome-free/js/brands'

// start the Stimulus application
import './bootstrap';

// require jQuery
const $ = require('jquery');

// create jQuery global
global.$ = global.jQuery = $

import zoomPlugin from 'chartjs-plugin-zoom';
import { Colors } from 'chart.js';

document.addEventListener('chartjs:init', function (event) {
    const Chart = event.detail.Chart;
    Chart.register(zoomPlugin);
    Chart.register(Colors);
});
=======
import './bootstrap.js';
// /assets/app.js

<<<<<<< ours
// import the main style sheet
import './styles/main.scss';

// import bootstrap js
import 'bootstrap';

// fontawesome
import '@fortawesome/fontawesome-free/js/fontawesome'
import '@fortawesome/fontawesome-free/js/solid'
import '@fortawesome/fontawesome-free/js/regular'
import '@fortawesome/fontawesome-free/js/brands'

// start the Stimulus application
import './bootstrap';

// require jQuery
const $ = require('jquery');

// create jQuery global
global.$ = global.jQuery = $

import zoomPlugin from 'chartjs-plugin-zoom';
import { Colors } from 'chart.js';

document.addEventListener('chartjs:init', function (event) {
    const Chart = event.detail.Chart;
    Chart.register(zoomPlugin);
    Chart.register(Colors);
});
>>>>>>> theirs
=======
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
>>>>>>> theirs
