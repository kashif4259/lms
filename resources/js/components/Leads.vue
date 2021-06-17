<template>
    <div>
        <div class="row mt-5" >
            <div class="col-12" v-if="$gate.admin()">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Search</h3>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <form @submit.prevent="search()" @keydown="form.errors.clear($event.target.name)">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="term" class="col-sm-2 col-form-label">Enter keyword</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="term" placeholder="Keyword" v-model="form.term" :class="{ 'is-invalid': form.errors.has('term') }">
                                        <div class="invalid-feedback" v-if="form.errors.has('term')">
                                            {{form.errors.get('term')}}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="location" class="col-sm-2 col-form-label">City/Zipcode</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="location" placeholder="Location" v-model="form.location" :class="{ 'is-invalid': form.errors.has('location') }">
                                        <div class="invalid-feedback" v-if="form.errors.has('location')">
                                            {{form.errors.get('location')}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-info float-right" :disabled="form.errors.any()">Search</button>
                            </div>
                        </form>
                    </div><!-- /.card-body -->
                </div><!-- /.card -->
            </div> <!-- /.col-12 -->
            <div class="col-12" v-if="!$gate.admin()">
                <not-found></not-found>
            </div>
        </div>
        <div class="row mt-5" >
            <div class="col-12" v-if="$gate.admin()">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Leads Management</h3>
                        <div class="card-tools">
                            
                        </div>
                    </div><!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Url</th>
                                <th>Display Phone</th>
                                <th>Phone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(lead, index) in leads.items" :key="index">
                                <td>{{lead.sr_no}}</td>
                                <td valign="center">{{lead.name}}</td>
                                <td>
                                    <img class="img-circle" :src="lead.image_url" width="100" height="100" alt="User Avatar">
                                </td>
                                <td valign="center"><a :href="lead.url" target="_blank">View on yelp</a></td>
                                <td valign="center">{{lead.display_phone}}</td>
                                <td valign="center">{{lead.phone}}</td>
                                <td></td>
                            </tr>
                        </tbody>
                        </table>
                        <div class="text-center" v-if="moreExists">
                            <button type="button" class="btn btn-primary btn-sm" v-on:click="search">
                                <span class="fa fa-arrow-down"></span>Load More
                            </button>
                        </div>
                    </div><!-- /.card-body -->
                    <div class="card-footer">
                        <!-- <pagination :data="users" @pagination-change-offset="getUsers">
                            <span slot="prev-nav">&lt; Previous</span>
                            <span slot="next-nav">Next &gt;</span>
                        </pagination> -->
                    </div>
                </div><!-- /.card -->
            </div><!-- /.col-12 -->
            <div class="col-12" v-if="!$gate.admin()">
                <not-found></not-found>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                leads: [],
                moreExists: false,
                form: new Form({
                    term: '',
                    location: '',
                    page: 0,
                    sr_no: 1
                })
            }
        },
        methods: {
            search(){
                this.$Progress.start();
                this.form.post('leads/yelp/find', false)
                    .then((response) => {
                        let message = response.data.message;
                        let resdata = response.data.data;
                        let page = resdata.page;
                        let total = resdata.total - page;
                        this.form.sr_no = resdata.sr_no;
                        if(this.moreExists){
                            resdata.items.forEach(data => {
                                this.leads.items.push(data);
                            });
                        }else{
                            this.leads = resdata;
                        }
                        
                        if(page < total){
                            this.moreExists = true;
                            this.form.page = page;
                        }else
                        {
                            this.moreExists = false;
                        }
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
            }
        },
        mounted() {
            this.form.reset();
        },
        created() {
          
        }
    }
</script>
