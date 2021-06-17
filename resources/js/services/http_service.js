import axios from 'axios';
import config from '../config/settings.js';

export function http() {
    return axios.create({
        baseURL: config.apiUrl
    });
}