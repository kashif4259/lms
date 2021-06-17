
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import moment from 'moment';
window.Vue = require('vue');

// import { Errors } from './utilities/Errors';

import Gate from './Gate';

Vue.prototype.$gate = new Gate(window.user);

import Swal from 'sweetalert2'

window.Swal = Swal;

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  })

  window.Toast = Toast;

import Vue from 'vue';

// window.Errors = new Errors();


Vue.component('pagination', require('laravel-vue-pagination'));


import VueRouter from 'vue-router'

Vue.use(VueRouter)

import VueProgressBar from 'vue-progressbar'
import _ from 'lodash';

// import config from '../config/settings.js';

import Form from './core/Form';

Vue.use(VueProgressBar, {
    color: 'rgb(143, 255, 199)',
    failedColor: 'red',
    height: '2px'
})

let routes = [
    { path: '/dashboard', component: require('./components/Dashboard.vue').default },
    // { path: '/developer', component: require('./components/Developer.vue').default },
    // { path: '/departments', component: require('./components/Departments.vue').default },
    { path: '/profile', component: require('./components/Profile.vue').default },
    { path: '/users', component: require('./components/Users.vue').default },
    { path: '/leads', component: require('./components/Leads.vue').default },
    { path: '*', component: require('./components/404.vue').default }
]

const router = new VueRouter({
    mode: 'history',
    routes // short for `routes: routes`
})

Vue.filter('uppText', function (text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
});

Vue.filter('readableDate', function (date) {
    return moment(date).format('MMMM Do YYYY, h:mm:ss a');
});

Vue.component(
    'not-found',
    require('./components/404.vue').default
);



/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

window.Fire = new Vue();

// window.config = config;

window.Form = Form;

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    data: {
        search: '',
    },

    methods: {
        searchit: _.debounce(() => {
            Fire.$emit('searching');
        }, 1000)
    }
});
