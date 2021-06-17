import Errors from './Errors';

class Form {
    constructor(data) {
        this.apiBaseUrl = 'http://127.0.0.1:8000/api/';
        this.originalData = data;

        for(let field in data) {
            this[field] = data[field];
        }

        this.errors = new Errors();
    }

    data() {

        let data = {};

        for(let property in this.originalData) {
            data[property] = this[property];
        }
        // let data = Object.assign({}, this);

        // delete data.originalData;

        // delete data.errors;

        return data;
    }

    fill(data) {
        Object.keys(data).forEach(key=>{
            if(this.originalData.hasOwnProperty(key)) {
                this[key] = data[key];
            }
        });
    }

    reset() {
        for(let field in this.originalData) {
            this[field] = '';
        }

        this.errors.clear();

    }

    post(url, formReset) {
        return this.sendRequestToServer('post', url);
    }

    put(url) {
        return this.sendRequestToServer('put', url);
    }

    delete(url) {
        return this.sendRequestToServer('delete', url);
    }

    get(url, page, query) {
        let term = '';
        if(page) {
            term += '?page='+page;
        }
        if(query) {
            term += '&q='+query;
        }
        url += term;
        return this.sendRequestToServer('get', url);
    }

    sendRequestToServer(requestType, endpoint, formReset) {
        return new Promise((resovle, reject) => {
            axios[requestType](this.apiBaseUrl+endpoint, this.data())
                // .then(this.onSuccess.bind(this))
                // .catch(this.onFail.bind(this))
                .then(response => {
                    
                    this.onSuccess(response, formReset);

                    resovle(response);
                })
                .catch(errors => {

                    this.onFail(errors);

                    reject(errors);
                });
        });
        

    }

    onSuccess(response, formReset) {
        // let message = response.data.message;
        // Fire.$emit('loadUsers');
        // $('#addNew').modal('hide');
        // Toast.fire({
        //     icon: 'success',
        //     title: message
        // })
        if(formReset)
        {
            this.reset();
        }
    }

    onFail(errors) {
        let status_code = errors.response.status;
        if(status_code === 422){
            this.errors.record(errors.response.data.errors);
        }
        // switch (status_code) {
        //     case 422:
        //         this.errors.record(error.response.data.errors);
        //         break;
        //     default:
        //         Toast.fire({
        //             icon: 'error',
        //             title: error.response.data.message
        //         })
        // }
    }
}

export default Form;