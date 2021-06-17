export default class Gate{
    constructor(user) {
        this.user = user;
    }

    admin() {
        return this.user.role === 'admin';
    }

    teacher() {
        return this.user.role === 'teacher';
    }
}