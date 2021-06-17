<template>

        <div class="row mt-5" >
          <div class="col-12" v-if="$gate.admin()">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users Management</h3>

                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 150px;">
                      <button type="submit" class="btn btn-success" @click="FormModal(null)">Add New <i class="fas fa-user-plus fa-fw"></i></button>
                  </div>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Type</th>
                      <th>Register At</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(user, index) in users.data" :key="index">
                      <td>{{user.sr_no}}</td>
                      <td>{{user.name}}</td>
                      <td>{{user.email}}</td>
                      <td><span class="tag tag-success">{{user.role | uppText}}</span></td>
                      <td>{{user.created_at | readableDate}}</td>
                      <td>
                        <a href="javascript:void(0)" @click="FormModal(user)">
                            <i class="fas fa-edit"></i>
                        </a>
                        &nbsp;|&nbsp;
                        <a href="javascript:void(0)">
                            <i class="fas fa-trash" @click="deleteUser(user.id)"></i>
                        </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="users" @pagination-change-page="getUsers">
                    <span slot="prev-nav">&lt; Previous</span>
                    <span slot="next-nav">Next &gt;</span>
                </pagination>
              </div>
            </div>
            <!-- /.card -->
          </div>
            <div class="col-12" v-if="!$gate.admin()">
                <not-found></not-found>
            </div>
          <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true" ref="usermodal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNewLabel">{{editmode ? 'Update User' : 'Add New'}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode  ? update() : create()" @keydown="form.errors.clear($event.target.name)">
                    <div class="modal-body">
                        <div class="form-group">
                            <input v-model="form.name" type="text" name="name"
                                placeholder="Name"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                            <!-- <has-error :form="form" field="name"></has-error> -->
                            <div class="invalid-feedback" v-if="form.errors.has('name')">
                                {{form.errors.get('name')}}
                            </div>
                        </div>
                        <div class="form-group">
                            <input v-model="form.email" type="email" name="email"
                                placeholder="Email Address"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                            <!-- <has-error :form="form" field="email"></has-error> -->
                            <div class="invalid-feedback" v-if="form.errors.has('email')">
                                {{form.errors.get('email')}}
                            </div>
                        </div>
                        <div class="form-group">
                            <select name="role" v-model="form.role" 
                            id="role" class="form-control"
                            :class="{ 'is-invalid': form.errors.has('role') }">
                                <option value="">Select Role</option>
                                <option value="user">Teacher</option>
                            </select>
                            <!-- <has-error :form="form" field="role"></has-error> -->
                            <div class="invalid-feedback" v-if="form.errors.has('role')">
                                {{form.errors.get('role')}}
                            </div>
                        </div>
                        <div class="form-group" v-if="!editmode">
                            <input v-model="form.password" type="password" name="password"
                                placeholder="Password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                            <!-- <has-error :form="form" field="password"></has-error> -->
                            <div class="invalid-feedback" v-if="form.errors.has('password')">
                                {{form.errors.get('password')}}
                            </div>
                        </div>
                        <div class="form-group">
                            <textarea v-model="form.bio" type="bio" name="bio"
                                placeholder="Shor bio for user (Optional)"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                            <!-- <has-error :form="form" field="bio"></has-error> -->
                            <div class="invalid-feedback" v-if="form.errors.bio">
                                {{form.errors.get('bio')}}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" :class="[editmode?'btn btn-success':'btn btn-primary']" :disabled="form.errors.any()">{{editmode ? 'Update' : 'Create'}}</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
        </div>
</template>

<script>
    export default {
        data() {
            return {
                users: {},
                editmode: false,
                form: new Form({
                    id: '',
                    name: '',
                    email: '',
                    role: '',
                    password: '',
                    bio: ''
                })
            }
        },
        methods: {
            FormModal(user = null) {
                this.editmode = false;
                $('#addNew').modal('show');
                if(user !== null) {
                    this.editmode = true;
                    this.form.fill(user);
                }
            },
            create: async function(){
                this.$Progress.start();
                this.form.post('users/create')
                    .then((response) => {
                        let message = response.data.message;
                        Fire.$emit('loadUsers');
                        $('#addNew').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: message
                        });
                        this.$Progress.finish();
                    })
                    .catch((errors) => {
                        let status_code = errors.response.status;
                        if(status_code !== 422) {
                            Toast.fire({
                                icon: 'error',
                                title: errors.response.data.message
                            });
                        }
                        this.$Progress.fail();
                    });
            },
            update() {
                this.$Progress.start();
                this.form.put('users/'+this.form.id)
                .then((response) => {
                    let message = response.data.message;
                    Fire.$emit('loadUsers');
                    $('#addNew').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: message
                    });
                    this.$Progress.finish();
                })
                .catch((errors) => {
                    let status_code = errors.response.status;
                    if(status_code !== 422) {
                        Toast.fire({
                            icon: 'error',
                            title: errors.response.data.message
                        });
                    }
                    this.$Progress.fail();
                });
            },
            getUsers: async function(page = 1){
                if(this.$gate.admin())
                {
                    let query = this.$parent.search;
                    this.form.get('users', page, query)
                        .then((response) => {
                            this.users = response.data.data;
                        })
                        .catch((errors) => {
                            Toast.fire({
                                icon: 'error',
                                title: errors.response.data.message
                            });
                        });
                }
            },

            deleteUser(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.form.delete('users/delete/'+id)
                        .then(response => {
                            let message = response.data.message;
                            Swal.fire(
                            'Deleted!',
                            message,
                            'success'
                            );
                            Fire.$emit('loadUsers');
                        })
                        .catch(() => {
                            Swal("Faild!", "Something went wrong!");
                        })
                    }
                })
            },
            formReset() {
                this.form.reset();
            }
        },
        mounted() {
            $(this.$refs.usermodal).on("hidden.bs.modal", this.formReset)
        },
        created() {
            if(this.$parent.search == ''){
                this.getUsers();
            }
            this.$parent.search = '';
            
            Fire.$on('searching', () => {
                this.getUsers();
            });

            
            Fire.$on('loadUsers', () => {
                this.getUsers();
            });
            // setInterval(() => this.getUsers(), 5000);
        }
    }
</script>
