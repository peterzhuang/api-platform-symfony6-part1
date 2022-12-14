/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// import './styles/app.css';

// start the Stimulus application
// import './bootstrap';

import Vue from 'vue';
import CheeseWhizApp from './js/components/CheeseWhizApp';
import 'bootstrap/dist/css/bootstrap.css';

Vue.component('cheese-whiz-app', CheeseWhizApp);

const app = new Vue({
    el: '#cheese-app'
});

