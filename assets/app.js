// /assets/app.js

// import './styles/app.css';
// import './styles/asset_collection.css';

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

// https://stackoverflow.com/questions/63082529/how-to-properly-introduce-a-light-dark-mode-in-bootstrap
document.getElementById('btnSwitch').addEventListener('click',()=>{
    if (document.documentElement.getAttribute('data-bs-theme') == 'dark') {
        document.documentElement.setAttribute('data-bs-theme','light')
    }
    else {
        document.documentElement.setAttribute('data-bs-theme','dark')
    }
})
