<div id="app">
  <div class="container">
  <div class="row">
    <div class="col-12">
      <h2>Image Cop</h2>
      
    </div>
  </div>

  <div v-if="loading" class="row">
        <div class="col-4">
            <img class="mx-auto d-block ic-loading" src="/wp-content/plugins/imagecop/lib/assets/ic_loading2.gif">  
        </div>
    </div>

      <div v-if="!loading" class="row">
      <div class="col-xs-6">
        <div class="card">
          <div class="card-content">
          
            <div class="form-group">
              <label for="bucket"><strong>AWS Bucket</strong></label>
              <input v-model="imageCop.bucket" type="text" class="form-control" name="bucket" id="bucket" aria-describedby="helpId" placeholder="AWS Bucket">
              <small id="helpId" class="form-text text-muted">The bucket used for media</small>
            </div>

            <div class="form-group">
            <label for="bucket"><strong>S3 Upload Folder</strong></label>
            <div class="input-group">
            
              <div class="input-group-prepend">
              <span class="input-group-text" id="basic-addon3">{{ imageCop.bucket }}/</span>
            </div>
                <input v-model="imageCop.upload_folder" type="text" class="form-control" name="" id="" aria-describedby="helpId" placeholder="">
                
              </div>
              <small id="helpId" class="form-text text-muted">This is the target folder all media will be uploaded to.</small>
            </div>

            <div class="form-group">
            <label for="bucket"><strong>S3 Compressed Folder</strong></label>
            <div class="input-group">
            
              <div class="input-group-prepend">
              <span class="input-group-text" id="basic-addon3">{{ imageCop.bucket }}/</span>
            </div>
                <input v-model="imageCop.compressed_folder" type="text" class="form-control" name="" id="" aria-describedby="helpId" placeholder="">
                
              </div>
              <small id="helpId" class="form-text text-muted">After files are uploaded to the upload bucket, they will be compressed and served from this <strong>public</strong> folder.</small>
            </div>

            <a href="#" @click="saveOptions" class="btn btn-primary">Save</a>
          </div>
          </div>
        </div>
      </div>
  </div>
</div>
<script>
var app = new Vue({
  el: '#app',
  data: {
    loading: false,
    imageCop: {
      bucket: '',
      upload_folder: '',
      compressed_folder: '',
      keep_local_files: false,
    },
    api: '<?php echo get_rest_url() . 'image-cop/v1/options'; ?>'
  },

  mounted(){
    this.getOptions();
  },

  methods: {
    getOptions(){
      this.loading = true;
      axios.get(this.api).then(response => {
        this.imageCop = response.data;
        this.loading = false;
      }).catch( err => {
        console.log(err);
        this.loading = false;
      })
    },

    saveOptions(){

      this.loading = true;
      var params = new URLSearchParams();
      params.append('bucket', this.imageCop.bucket);
      params.append('upload_folder', this.imageCop.upload_folder);
      params.append('compressed_folder', this.imageCop.compressed_folder);
      params.append('keep_local_files', this.imageCop.keep_local_files);


      axios.post(this.api, params ).then( response => {
        this.imageCop = response.data;
        this.loading = false;
        console.log(response.data);
      }).catch( err => {
        console.log(err);
        this.loading = false;
      })
    }
  },
})

</script>
<style>
  #app {
    padding:40px;
  }
.ic-loading {
  margin-top:75px;
}
</style>