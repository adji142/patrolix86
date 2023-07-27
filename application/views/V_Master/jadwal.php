<?php
    require_once(APPPATH."views/parts/Header.php");
    require_once(APPPATH."views/parts/Sidebar.php");
    $active = 'dashboard';
    // echo $NIK;
?>
<!-- page content -->
  <div class="right_col" role="main">
    <div class="">

      <div class="clearfix"></div>

      <div class="row">
        <div class="col-md-12 col-sm-12  ">
          <div class="x_panel">
            <div class="x_title">
              <h2>Jadwal</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <div class="row">
                <div class="col-md-3 col-sm-12  form-group">
                  Tgl Awal
                  <input type="date" id="FromDate" name="FromDate" class="form-control">
                </div>
                <div class="col-md-3 col-sm-12  form-group">
                  Tgl Akhir
                  <input type="date" id="ToDate" name="ToDate" class="form-control">
                </div>
                <div class="col-md-3 col-sm-12  form-group">
                  <!-- <input type="text" placeholder="NIK / Nama" class="form-control"> -->
                  <br>
                  <button class="btn btn-primary" id="btSearch">Cari Data</button>
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
                <input type="text" name="NIK" id="NIK" required="" placeholder="Nomer Induk Karyawan" class="form-control " readonly="" value="<?php echo $NIK ?>">
                <input type="hidden" name="formtype" id="formtype" value="add">
                <input type="hidden" name="RecordOwnerID" id="RecordOwnerID" value="<?php echo $this->session->userdata('RecordOwnerID') ?>">
                <input type="hidden" name="LocationID" id="LocationID">
                <input type="hidden" name="id" id="id">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nama Karyawan <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="NamaSecurity" id="NamaSecurity" required="" placeholder="Nama Karyawan" class="form-control " readonly="">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Tanggal <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="date" name="Tanggal" id="Tanggal" required="" placeholder="Nama Karyawan" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Shift <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <select class="form-control " name="Shift" id="Shift">
                  <option value="-1">Pilih Shift</option>
                </select>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Kehadiran <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <select id="StatusKehadiran" name="StatusKehadiran" class="form-control">
                  <option value="-1">Pilih Kehadiran</option>

                  <?php 
                    $rs = $this->ModelsExecuteMaster->GetData('tstatuskehadiran')->result();

                    foreach ($rs as $key) {
                      echo "<option value = '".$key->id."'>".$key->Nama."</option>";
                    }
                  ?>
                </select>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Keterangan
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="Keterangan" id="Keterangan" placeholder="Keterangan" class="form-control ">
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
      getDate();
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Jadwal/Read",
        data: {'NIK':$('#NIK').val(), 'RecordOwnerID': RecordOwnerID,'LocationID':$('#KodeLokasi').val(),'TglAwal': $('#FromDate').val(), 'TglAkhir':$('#ToDate').val()},
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
            url     : '<?=base_url()?>C_Jadwal/CRUD',
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
        url: "<?=base_url()?>C_Jadwal/Read",
        data: {'NIK':$('#NIK').val(), 'RecordOwnerID': RecordOwnerID,'LocationID':$('#KodeLokasi').val(),'TglAwal': $('#FromDate').val(), 'TglAkhir':$('#ToDate').val()},
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
        }
      });
    });

    function GetData(id) {
      var where_field = 'id';
      var where_value = id;
      var table = 'users';
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Jadwal/Find",
        data: {'id':id, 'RecordOwnerID':RecordOwnerID, 'NIK':$('#NIK').val()},
        dataType: "json",
        success: function (response) {
          $.each(response.data,function (k,v) {
            // $('#KodePenyakit').val(v.KodePenyakit).change;
            $('#NIK').val(v.NIK);
            $('#id').val(v.id);

            $('#Tanggal').val(v.Tanggal);
            $('#Shift').val(v.Jadwal).change();
            $('#StatusKehadiran').val(v.StatusKehadiran).change();
            $('#Keterangan').val(v.Keterangan);

            $('#formtype').val("edit");
            $('#modal_').modal('show');
          });
        }
      });
    }
    function getDate() {
      var now = new Date();

      var day = ("0" + now.getDate()).slice(-2);
      var month = ("0" + (now.getMonth() + 1)).slice(-2);

      var today = now.getFullYear()+"-"+month+"-01";
      var lastDayofYear = now.getFullYear()+"-"+month+"-"+day;

      $('#FromDate').val(today);
      $('#ToDate').val(lastDayofYear);
    }
    function bindGrid(data) {

      $("#gridContainer").dxDataGrid({
        allowColumnResizing: true,
            dataSource: data,
            keyExpr: "id",
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
                    dataField: "id",
                    caption: "id",
                    allowEditing:false,
                    visible:false
                },
                {
                    dataField: "Hari",
                    caption: "Hari",
                    allowEditing:false
                },
                {
                    dataField: "Tanggal",
                    caption: "Tanggal",
                    allowEditing:false
                },
                {
                    dataField: "NamaShift",
                    caption: "Shift",
                    allowEditing:false
                },
                {
                    dataField: "StatusKehadiran",
                    caption: "Status",
                    allowEditing:false
                },
                {
                    dataField: "Keterangan",
                    caption: "Keterangan",
                    allowEditing:false
                },
            ],
            onEditingStart: function(e) {
              $.ajax({
                async:false,
                type: "post",
                url: "<?=base_url()?>C_Security/Read",
                data: {'NIK':$('#NIK').val(), 'RecordOwnerID':RecordOwnerID},
                dataType: "json",
                success: function (response) {
                  $.each(response.data,function (k,v) {
                    // $('#KodePenyakit').val(v.KodePenyakit).change;
                    // $('#NIK').val(v.NIK);
                    $('#NamaSecurity').val(v.NamaSecurity);
                    $('#JoinDate').val(v.JoinDate);
                    $('#LocationID').val(v.LocationID);
                    $('#RecordOwnerID').val(v.RecordOwnerID);
                    $('#modal_').modal('show');
                  });

                  $.ajax({
                    async:false,
                    type: "post",
                    url: "<?=base_url()?>C_Shift/Read",
                    data: {
                      'RecordOwnerID' :$('#RecordOwnerID').val(),
                      'LocationID'    :$('#LocationID').val()
                    },
                    dataType: "json",
                    success: function (rs) {
                      // bindGrid(response.data);
                      $('#Shift').empty();
                      if (rs.data.length > 0) {
                        $('#Shift').append('<option value="-1">Pilih Shift</option>');
                        $.each(rs.data,function (k,v) {
                          $('#Shift').append('<option value="' + v.id + '">' + v.NamaShift + '</option>');
                        });
                      }
                      else{
                        $('#Shift').append('<option value="-1">Pilih Shift</option>');
                      }
                    }
                  });
                }
              });
              GetData(e.data.id);
            },
            onInitNewRow: function(e) {
                // logEvent("InitNewRow");
              // var NIK = "<?php echo $NIK; ?>";
              $.ajax({
                async:false,
                type: "post",
                url: "<?=base_url()?>C_Security/Read",
                data: {'NIK':$('#NIK').val(), 'RecordOwnerID':RecordOwnerID},
                dataType: "json",
                success: function (response) {
                  $.each(response.data,function (k,v) {
                    // $('#KodePenyakit').val(v.KodePenyakit).change;
                    // $('#NIK').val(v.NIK);
                    $('#NamaSecurity').val(v.NamaSecurity);
                    $('#JoinDate').val(v.JoinDate);
                    $('#LocationID').val(v.LocationID);
                    $('#RecordOwnerID').val(v.RecordOwnerID);
                    $('#modal_').modal('show');
                  });

                  $.ajax({
                    async:false,
                    type: "post",
                    url: "<?=base_url()?>C_Shift/Read",
                    data: {
                      'RecordOwnerID' :$('#RecordOwnerID').val(),
                      'LocationID'    :$('#LocationID').val()
                    },
                    dataType: "json",
                    success: function (rs) {
                      // bindGrid(response.data);
                      $('#Shift').empty();
                      if (rs.data.length > 0) {
                        $('#Shift').append('<option value="-1">Pilih Shift</option>');
                        $.each(rs.data,function (k,v) {
                          $('#Shift').append('<option value="' + v.id + '">' + v.NamaShift + '</option>');
                        });
                      }
                      else{
                        $('#Shift').append('<option value="-1">Pilih Shift</option>');
                      }
                    }
                  });
                }
              });
                // $('#modal_').modal('show');
            },
            onRowRemoving: function(e) {
              id = e.data.id;
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
                      url     : '<?=base_url()?>C_Jadwal/CRUD',
                      data    : {'id':id,'NIK':$('#NIK').val(),'formtype':'delete', 'RecordOwnerID':RecordOwnerID},
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