import {http} from './http_service.js';

export function getMembers(page, query) {
    console.log(query);
    let term = '?page='+page;
    if(query)
    {
        term += '&q='+query;
    }
    return http().get('/users'+term);
}

export function createUser(data) {
    console.log(data+"from service");
    return http().post('/users/create', data)
}

export function updateUser(id) {
    return http().put(`/${id}`);
}