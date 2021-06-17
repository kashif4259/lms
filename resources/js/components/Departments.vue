<template>

        <div class="row mt-5" >
          <div class="col-12" v-if="$gate.admin()">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Departments Management</h3>

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
                      <th>Person In-Charge</th>
                      <th>Register At</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(department, index) in departments.data" :key="index">
                      <td>{{department.sr_no}}</td>
                      <td>{{department.name}}</td>
                      <td>{{department.dean}}</td>
                      <td>{{department.created_at | readableDate}}</td>
                      <td>
                        <a href="javascript:void(0)" @click="FormModal(department)">
                            <i class="fas fa-edit"></i>
                        </a>
                        &nbsp;|&nbsp;
                        <a href="javascript:void(0)">
                            <i class="fas fa-trash" @click="deleteDepartment(department.id)"></i>
                        </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="departments" @pagination-change-page="getDepartments">
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
          <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true" ref="departmentmodal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNewLabel">{{editmode ? 'Update Department' : 'Add New'}}</h5>
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
                            <input v-model="form.dean" type="text" name="dean"
                                placeholder="Person Incharge"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('dean') }">
                            <!-- <has-error :form="form" field="name"></has-error> -->
                            <div class="invalid-feedback" v-if="form.errors.has('dean')">
                                {{form.errors.get('dean')}}
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
                departments: {},
                editmode: false,
                form: new Form({
                    id: '',
                    name: '',
                    dean: ''
                })
            }
        },
        methods: {
            FormModal(department = null) {
                this.editmode = false;
                $('#addNew').modal('show');
                if(department !== null) {
                    this.editmode = true;
                    this.form.fill(department);
                }
            },
            create: async function(){
                this.$Progress.start();
                this.form.post('departments/create')
                    .then((response) => {
                        let message = response.data.message;
                        Fire.$emit('loadDepartments');
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
                this.form.put('departments/'+this.form.id)
                .then((response) => {
                    let message = response.data.message;
                    Fire.$emit('loadDepartments');
                    $('#addNew').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: message
                    });
                    //  Swal.fire(
                    //     'Updated!',
                    //     message,
                    //     'success'
                    // );
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
            getDepartments: async function(page = 1){
                if(this.$gate.admin())
                {
                    let query = this.$parent.search;
                    this.form.get('departments', page, query)
                        .then((response) => {
                            this.departments = response.data.data;
                        })
                        .catch((errors) => {
                            Toast.fire({
                                icon: 'error',
                                title: errors.response.data.message
                            });
                        });
                }
            },

            deleteDepartment(id) {
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
                        //send ajax request to delete
                        this.form.delete('departments/delete/'+id)
                        .then(response => {
                            let message = response.data.message;
                            Swal.fire(
                            'Deleted!',
                            message,
                            'success'
                            );
                            Fire.$emit('loadDepartments');
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
            $(this.$refs.departmentmodal).on("hidden.bs.modal", this.formReset)
        },
        created() {
            this.$parent.search = '';
            Fire.$on('searching', () => {
                this.getDepartments();
            });

            this.getDepartments();
            Fire.$on('loadDepartments', () => {
                this.getDepartments();
            });
        }
    }
</script>
