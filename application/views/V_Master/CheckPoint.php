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
              <h2>Check Point</h2>
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
                <div class="col-md-6 col-sm-12  ">
                  <br>
                  <button class="btn btn-warning" id="btSearch">Cari</button>
                  <button class="btn btn-primary" id="btExportQR">Export QRCODE</button>
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
          <h4 class="modal-title" id="myModalLabel">Modal Check Point</h4>
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="post_" data-parsley-validate class="form-horizontal form-label-left">
            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Kode Check Point <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="KodeCheckPoint" id="KodeCheckPoint" required="" placeholder="Kode Check Point" class="form-control ">
                <input type="hidden" name="formtype" id="formtype" value="add">
                <input type="hidden" name="RecordOwnerID" id="RecordOwnerID" value="<?php echo $this->session->userdata('RecordOwnerID') ?>">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nama Check Point <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="NamaCheckPoint" id="NamaCheckPoint" required="" placeholder="Nama Check Point" class="form-control ">
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
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Keterangan <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="Keterangan" id="Keterangan" required="" placeholder="Keterangan" class="form-control ">
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
    var RecordOwnerID = $('#RecordOwnerID').val();
    $(document).ready(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_CheckPoint/Read",
        data: {'KodeCheckPoint':'', 'RecordOwnerID': RecordOwnerID,'KodeLokasi':$('#KodeLokasi').val()},
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

      $.ajax({
            type    :'post',
            url     : '<?=base_url()?>C_CheckPoint/CRUD',
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
    $('#btExportQR').click(function () {
      // generateQRCode
      $('#btExportQR').text('Tunggu Sebentar.....');
      $('#btExportQR').attr('disabled',true);

      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_CheckPoint/generateQRCode",
        data: {'LocationID':$('#KodeLokasi').val()},
        dataType: "json",
        success: function (response) {
          // console.log(response);
          window.open(response.DownloadLink, "_blank");
          location.reload();
        }
      });
    })
    $('#btSearch').click(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_CheckPoint/Read",
        data: {'KodeCheckPoint':'', 'RecordOwnerID': RecordOwnerID,'KodeLokasi':$('#KodeLokasi').val()},
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
        }
      });
    })
    function GetData(id) {
      var where_field = 'id';
      var where_value = id;
      var table = 'users';
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_CheckPoint/Read",
        data: {'KodeCheckPoint':id, 'RecordOwnerID':RecordOwnerID},
        dataType: "json",
        success: function (response) {
          $.each(response.data,function (k,v) {
            // $('#KodePenyakit').val(v.KodePenyakit).change;
            $("#KodeCheckPoint").prop("disabled", true);
            $('#KodeCheckPoint').val(v.KodeCheckPoint);
            $('#NamaCheckPoint').val(v.NamaCheckPoint);
            $('#Keterangan').val(v.Keterangan);
            $('#LocationID').val(v.LocationID).change();
            $('#RecordOwnerID').val(v.RecordOwnerID);

            $('#formtype').val("edit");
            $('#modal_').modal('show');
          });
        }
      });
    }
    function bindGrid(data) {

      $("#gridContainer").dxDataGrid({
        allowColumnResizing: true,
            dataSource: data,
            keyExpr: "KodeCheckPoint",
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
                fileName: "Daftar Lokasi Patroli"
            },
            columns: [
                {
                    dataField: "KodeCheckPoint",
                    caption: "Kode Check Point",
                    allowEditing:false
                },
                {
                    dataField: "NamaCheckPoint",
                    caption: "Nama Check Point",
                    allowEditing:false
                },
                {
                    dataField: "NamaArea",
                    caption: "Lokasi",
                    allowEditing:false
                },
                {
                    dataField: "Keterangan",
                    caption: "Keterangan",
                    allowEditing:false
                },
            ],
            onEditingStart: function(e) {
                GetData(e.data.KodeCheckPoint);
            },
            onInitNewRow: function(e) {
                // logEvent("InitNewRow");
                $('#modal_').modal('show');
            },
            onRowRemoving: function(e) {
              id = e.data.KodeCheckPoint;
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
                      url     : '<?=base_url()?>C_CheckPoint/CRUD',
                      data    : {'KodeCheckPoint':id,'formtype':'delete', 'RecordOwnerID':RecordOwnerID},
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