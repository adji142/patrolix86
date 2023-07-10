<?php
    require_once(APPPATH."views/parts/Header.php");
    require_once(APPPATH."views/parts/Sidebar.php");
    $active = 'dashboard';
?>
<style type="text/css">
  .select2-container {
  width: 50% !important;
  }
</style>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12  ">
        <div class="x_panel">
          <div class="x_title">
            <h2>User</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            <form id="post_" data-parsley-validate class="form-horizontal form-label-left">
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nomor Induk Siswa <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="text" id="NIS" name="NIS" required="required" class="form-control" readonly="" value="<?php echo $this->session->userdata('username') ?>">
                  <input type="hidden" name="formtype" id="formtype" value="edit">
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nama Siswa <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="text" name="NamaSiswa" id="NamaSiswa" required="" placeholder="Nama Siswa" class="form-control ">
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Alamat <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <textarea class="form-control" rows="3" placeholder="Alamat SIswa" name="AlamatSiswa" id="AlamatSiswa"></textarea>
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nama Wali <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="text" name="NamaWali" id="NamaWali" required="" placeholder="Nama Wali" class="form-control ">
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Kelas <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <select class="form-control " name="Kelas" id="Kelas" disabled="">
                    <option value="">Pilih Kelas</option>
                    <?php
                      $rs = $this->ModelsExecuteMaster->GetData('tkelas')->result();

                      foreach ($rs as $key) {
                        echo "<option value = '".$key->id."'>".$key->NamaKelas."</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Jurusan <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <select class="form-control " name="Jurusan" id="Jurusan" disabled="">
                    <option value="">Pilih Jurusan</option>
                    <?php
                      $rs = $this->ModelsExecuteMaster->GetData('tjurusan')->result();

                      foreach ($rs as $key) {
                        echo "<option value = '".$key->id."'>".$key->NamaJurusan."</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Tempat Lahir <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="text" name="TempatLahir" id="TempatLahir" required="" placeholder="Tempat Lahir" class="form-control ">
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Tanggal Lahir <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="date" name="TglLahir" id="TglLahir" required="" placeholder="Tempat Lahir" class="date-picker form-control ">
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">No.Tlp Siswa(WhatsApp)
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="text" name="NoTlpSiswa" id="NoTlpSiswa" placeholder="No.Tlp Siswa(WhatsApp)" class="form-control ">
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">No.Tlp Wali(WhatsApp)
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="text" name="NoTlpWali" id="NoTlpWali" placeholder="No.Tlp Wali(WhatsApp)" class="form-control ">                
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Email
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="mail" name="Email" id="Email" placeholder="Email" class="form-control ">               
                </div>
              </div>
              <div class="item form-group">
                <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Angakatan <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 ">
                  <input type="text" name="TahunAngkatan" id="TahunAngkatan" required="" placeholder="Tahun Angkatan" class="form-control " readonly="">         
                </div>
              </div>
              <div class="item form-group">
                <button class="btn btn-primary" id="btn_Save">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
        <!-- /page content -->
<?php
  require_once(APPPATH."views/parts/Footer.php");
?>
<script type="text/javascript">
  $(function () {
    $(document).ready(function () {

      $.ajax({
        async : false,
        type: "post",
        url: "<?=base_url()?>C_Siswa/Read",
        data: {NIS : $('#NIS').val(),Kelas:'', Jurusan:'',textSearch:''},
        dataType: "json",
        success: function (response) {
          $.each(response.data,function (k,v) {
            // console.log(v.KelompokUsaha);
            // $('#KodePenyakit').val(v.KodePenyakit).change;
            $('#NIS').val(v.NIS);
            $('#NamaSiswa').val(v.NamaSiswa);
            $('#AlamatSiswa').val(v.AlamatSiswa);
            $('#NamaWali').val(v.NamaWali);
            $('#Kelas').val(v.Kelas).change();
            $('#Jurusan').val(v.Jurusan).change();
            $('#TempatLahir').val(v.TempatLahir);
            $('#TglLahir').val(v.TglLahir);
            $('#NoTlpSiswa').val(v.NoTlpSiswa);
            $('#NoTlpWali').val(v.NoTlpWali);
            $('#Email').val(v.Email);
            $('#TahunAngkatan').val(v.TahunAngkatan);

            $('#formtype').val("edit");

            $('#modal_').modal('show');
          });
        }
      });


    });

    $('#post_').submit(function (e) {
      $('#btn_Save').text('Tunggu Sebentar.....');
      $('#btn_Save').attr('disabled',true);

      e.preventDefault();
      var me = $(this);

      // console.log(me);
      $.ajax({
        type    :'post',
        url     : '<?=base_url()?>C_Siswa/CRUD',
        data    : me.serialize(),
        dataType: 'json',
        success : function (response) {
          if(response.success == true){
            Swal.fire({
              type: 'success',
              title: 'Horay..',
              text: 'Data Berhasil disimpan!',
              // footer: '<a href>Why do I have this issue?</a>'
            }).then((result)=>{
              location.reload();
            });
          }
          else{
            Swal.fire({
              type: 'error',
              title: 'Woops...',
              text: response.message,
              // footer: '<a href>Why do I have this issue?</a>'
            }).then((result)=>{
              $('#btn_Save').text('Save');
              $('#btn_Save').attr('disabled',false);
            });
          }
        }
      });
    });
  });
</script>