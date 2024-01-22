<?php
    require_once(APPPATH."views/parts/Header.php");
    require_once(APPPATH."views/parts/Sidebar.php");
    $active = 'dashboard';
?>
<!-- page content -->
  <div class="right_col" role="main">
    <div class="">

      <div class="clearfix"></div>

      <div class="row">
        <div class="col-md-12 col-sm-12  ">
          <div class="x_panel">
            <div class="x_title">
              <h2>Security</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <div class="row">
                <div class="col-md-4 col-sm-12  ">
                  Lokasi :
                  <select id="KodeLokasi" name="KodeLokasi" class="form-control">
                    <?php
                      $oParam = array();

                      if ($this->session->userdata('AreaUser') != "") {
                        $oParam = array(
                          'RecordOwnerID' => $this->session->userdata('RecordOwnerID'),
                          'id' => $this->session->userdata('AreaUser'),
                        );
                      }
                      else{
                        echo "<option value=''>Pilih Lokasi</option>";
                        $oParam = array(
                          'RecordOwnerID' => $this->session->userdata('RecordOwnerID')
                        );
                      }
                      // var_dump($oParam);
                      $rs = $this->ModelsExecuteMaster->FindData($oParam,'tlokasipatroli')->result();

                      foreach ($rs as $key) {
                        echo "<option value = '".$key->id."'>".$key->NamaArea."</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="col-md-4 col-sm-12  ">
                  Status :
                  <select id="Status" name="Status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                  </select>
                </div>
                <div class="col-md-4 col-sm-12  ">
                  <br>
                  <button class="btn btn-warning" id="btSearch">Cari</button>
                </div>
                <div class="col-md-12 col-sm-12  ">
                  <div class="dx-viewport demo-container">
                    <div id="data-grid-demo">
                      <div id="gridContainer">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /page content -->

  <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true" id="modal_">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Security</h4>
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="post_" data-parsley-validate class="form-horizontal form-label-left">
            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nomer Induk Karyawan <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="NIK" id="NIK" required="" placeholder="Nomer Induk Karyawan" class="form-control ">
                <input type="hidden" name="formtype" id="formtype" value="add">
                <input type="hidden" name="RecordOwnerID" id="RecordOwnerID" value="<?php echo $this->session->userdata('RecordOwnerID') ?>">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nama Karyawan <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="NamaSecurity" id="NamaSecurity" required="" placeholder="Nama Karyawan" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Tgl Bergabung <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="date" name="JoinDate" id="JoinDate" required="" placeholder="Nama Karyawan" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Lokasi Patroli <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <select id="LocationID" name="LocationID" class="form-control">
                  <option value="">Pilih Lokasi..</option>
                  <?php
                    $rs = $this->ModelsExecuteMaster->FindData(array('RecordOwnerID'=>$this->session->userdata('RecordOwnerID')),'tlokasipatroli')->result();

                    foreach ($rs as $key) {
                      echo "<option value = '".$key->id."'>".$key->NamaArea."</option>";
                    }
                  ?>
                </select>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Shift <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <select class="form-control " name="Shift" id="Shift">
                  <option value="">Pilih Shift</option>
                </select>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Status <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <select id="Status" name="Status" class="form-control">
                  <option value="1">Aktif</option>
                  <option value="0">Tidak Aktif</option>
                </select>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Foto <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <div id="my_camera"></div>
                <div id="image_result"></div>
                <textarea id = "image_base64" name = "image_base64" style="display:none"> </textarea>
                <br>
                <a class="btn btn-success" id = "bt_take_picture"> Take Picture </a>
                <!-- <a class="btn btn-warning" id = "bt_Upload"> Upload </a> -->
                <input type="file" id="Attachment" name="Attachment" accept=".jpg" class="btn btn-warning"/>
                <a class="btn btn-danger" id = "bt_clear"> Clear </a>
              </div>
            </div>

            <div class="item" form-group>
              <button class="btn btn-primary" id="btn_Save">Save</button>
            </div>
          </form>
        </div>
        <!-- <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          
        </div> -->

      </div>
    </div>
  </div>
<?php
  require_once(APPPATH."views/parts/Footer.php");
?>
<script src="https://cdn.jsdelivr.net/timepicker.js/latest/timepicker.min.js"></script>
<link href="https://cdn.jsdelivr.net/timepicker.js/latest/timepicker.min.css" rel="stylesheet"/>

<script type="text/javascript">
  $(function () {
    var _URL = window.URL || window.webkitURL;
    var _URLePub = window.URL || window.webkitURL;
    var RecordOwnerID = $('#RecordOwnerID').val();
    Webcam.set({
			width: 320,
			height: 240,
			image_format: 'jpeg',
			jpeg_quality: 90
		});
		Webcam.attach( '#my_camera' );
    $(document).ready(function () {
      $('#Status').val("1").change();
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Security/Read",
        data: {'NIK':'', 'RecordOwnerID': RecordOwnerID,'LocationID':$('#KodeLokasi').val(), 'Status':$('#Status').val()},
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
        }
      });

      
    });
    $('#post_').submit(function (e) {
      $('#btn_Save').text('Tunggu Sebentar.....');
      $('#btn_Save').attr('disabled',true);

      e.preventDefault();
      var me = $(this);
      // var form_data = new FormData(this);

      $.ajax({
            type    :'post',
            url     : '<?=base_url()?>C_Security/CRUD',
            data    : me.serialize(),
            dataType: 'json',
            success : function (response) {
              if(response.success == true){
                $('#modal_').modal('toggle');
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
                $('#modal_').modal('toggle');
                Swal.fire({
                  type: 'error',
                  title: 'Woops...',
                  text: response.message,
                  // footer: '<a href>Why do I have this issue?</a>'
                }).then((result)=>{
                  $('#modal_').modal('show');
                  $('#btn_Save').text('Save');
                  $('#btn_Save').attr('disabled',false);
                });
              }
            }
          });
        });
    $('.close').click(function() {
      location.reload();
    });
    $('#btSearch').click(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Security/Read",
        data: {'KodeCheckPoint':'', 'RecordOwnerID': RecordOwnerID,'LocationID':$('#KodeLokasi').val(),'Status':$('#Status').val()},
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
        }
      });
    });

    $('#LocationID').change(function () {
      $.ajax({
        async:false,
        type: "post",
        url: "<?=base_url()?>C_Shift/Read",
        data: {
          'RecordOwnerID' :$('#RecordOwnerID').val(),
          'LocationID'    :$('#LocationID').val()
        },
        dataType: "json",
        success: function (response) {
          // bindGrid(response.data);
          $('#Shift').empty();
          if (response.data.length > 0) {
            $('#Shift').append('<option value="-1">Pilih Shift</option>');
            $.each(response.data,function (k,v) {
              $('#Shift').append('<option value="' + v.id + '">' + v.NamaShift + '</option>');
            });
          }
          else{
            $('#Shift').append('<option value="-1">Pilih Shift</option>');
          }
        }
      });
    })
    function GetData(id) {
      var where_field = 'id';
      var where_value = id;
      var table = 'users';
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Security/Read",
        data: {'NIK':id, 'RecordOwnerID':RecordOwnerID},
        dataType: "json",
        success: function (response) {
          $.each(response.data,function (k,v) {
            // $('#KodePenyakit').val(v.KodePenyakit).change;
            $("#NIK").prop("disabled", true);
            $('#NIK').val(v.NIK);
            $('#NamaSecurity').val(v.NamaSecurity);
            $('#JoinDate').val(v.JoinDate);
            $('#LocationID').val(v.LocationID).change();
            $('#Status').val(v.Status).change();
            $('#RecordOwnerID').val(v.RecordOwnerID);
            $('#Shift').val(v.Shift).change();

            $('#image_result').html("<img src ='"+v.Image+"' width = '400'> ");
            $('#image_base64').val(v.Image);

            $('#formtype').val("edit");
            $('#modal_').modal('show');
          });
        }
      });
    }

    $('#bt_take_picture').click(function(){
      Webcam.snap(function(data_uri){
        // console.log(data_uri);
        $('#my_camera').css('display','none');
        $('#image_result').html("<img src ='"+data_uri+"' width = '400'> ");
        // $('#result').css('display','');
        $('#image_base64').val(data_uri);
      })
    });
    $('#bt_clear').click(function(){
      $('#my_camera').css('display','');
      $('#image_base64').val("");
      // $('#image_result').css('display','none');
    });

    $("#Attachment").change(function(){
      var file = $(this)[0].files[0];
      img = new Image();
      img.src = _URL.createObjectURL(file);
      var imgwidth = 0;
      var imgheight = 0;
      img.onload = function () {
        imgwidth = this.width;
        imgheight = this.height;
        $('#width').val(imgwidth);
        $('#height').val(imgheight);
      }
      readURL(this);
      encodeImagetoBase64(this);
      // alert("Current width=" + imgwidth + ", " + "Original height=" + imgheight);
    });

    function readURL(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
          
        reader.onload = function (e) {
          console.log(e.target.result);
            // $('#image_result').attr('src', e.target.result);
            $('#image_result').html("<img src ='"+e.target.result+"' width = '400'> ");
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
    function encodeImagetoBase64(element) {
      $('#image_base64').val('');
        var file = element.files[0];
        var reader = new FileReader();
        reader.onloadend = function() {
          // $(".link").attr("href",reader.result);
          // $(".link").text(reader.result);
          $('#image_base64').val(reader.result);
        }
        reader.readAsDataURL(file);
    }
    function bindGrid(data) {

      $("#gridContainer").dxDataGrid({
        allowColumnResizing: true,
            dataSource: data,
            keyExpr: "NIK",
            showBorders: true,
            allowColumnReordering: true,
            allowColumnResizing: true,
            columnAutoWidth: true,
            showBorders: true,
            paging: {
                enabled: false
            },
            editing: {
                mode: "row",
                allowAdding:true,
                allowUpdating: true,
                allowDeleting: true,
                texts: {
                    confirmDeleteMessage: ''  
                }
            },
            searchPanel: {
                visible: true,
                width: 240,
                placeholder: "Search..."
            },
            export: {
                enabled: true,
                fileName: "Daftar Security"
            },
            columns: [
                {
                    dataField: "NIK",
                    caption: "Nomer Induk Karyawan",
                    allowEditing:false
                },
                {
                    dataField: "NamaSecurity",
                    caption: "Nama",
                    allowEditing:false
                },
                {
                    dataField: "JoinDate",
                    caption: "Join Date",
                    allowEditing:false
                },
                {
                    dataField: "NamaShift",
                    caption: "Shift",
                    allowEditing:false
                },
                {
                    dataField: "NamaArea",
                    caption: "Nama Area",
                    allowEditing:false
                },
                {
                    dataField: "FileItem",
                    caption: "Action",
                    allowEditing:false,
                    cellTemplate: function(cellElement, cellInfo) {
                      LinkAccess = "<a href = '<?=base_url()?>jadwal/"+cellInfo.data.NIK+"' class='btn btn-warning'>Atur Jadwal</a>";
                      // console.log();
                      cellElement.append(LinkAccess);
                  }
                }
            ],
            onEditingStart: function(e) {
                GetData(e.data.NIK);
            },
            onInitNewRow: function(e) {
                // logEvent("InitNewRow");
                $('#modal_').modal('show');
            },
            onRowRemoving: function(e) {
              id = e.data.NIK;
              Swal.fire({
                title: 'Apakah anda yakin?',
                text: "anda akan menghapus data di baris ini !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
              }).then((result) => {
                if (result.value) {

                  $.ajax({
                      type    :'post',
                      url     : '<?=base_url()?>C_Security/CRUD',
                      data    : {'NIK':id,'formtype':'delete', 'RecordOwnerID':RecordOwnerID},
                      dataType: 'json',
                      success : function (response) {
                        if(response.success == true){
                          Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                      ).then((result)=>{
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
                            location.reload();
                          });
                        }
                      }
                    });
                  
                }
                else{
                  location.reload();
                }
              })
            },
        });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }
  });
</script>