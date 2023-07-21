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
              <h2>Shift</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <div class="row">
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
          <h4 class="modal-title" id="myModalLabel">Shift</h4>
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="post_" data-parsley-validate class="form-horizontal form-label-left">
            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nama Shift <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="NamaShift" id="NamaShift" required="" placeholder="Nama Shift" class="form-control ">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="formtype" id="formtype" value="add">
                <input type="hidden" name="RecordOwnerID" id="RecordOwnerID" value="<?php echo $this->session->userdata('RecordOwnerID') ?>">
                <input type="hidden" name="LocationID" id="LocationID" value="<?php echo $LocationID ?>">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Mulai Jam Kerja <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="time" name="MulaiBekerja" id="MulaiBekerja" required="" placeholder="Mulai Patroli" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Selesai Jam Kerja <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="time" name="SelesaiBekerja" id="SelesaiBekerja" required="" placeholder="Selesai Patroli" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Interval <span class="required">*</span>
              </label>
              <div class="col-md-3 col-sm-3  form-group">
                <input type="number" name="IntervalPatroli" id="IntervalPatroli" required="" placeholder="Interval" class="form-control ">
              </div>
              <div class="col-md-3 col-sm-3  form-group">
                <select class="form-control" id="IntervalType" name="IntervalType">
                  <option value="DAY">Hari</option>
                  <option value="HOUR">Jam</option>
                  <!-- <option value="MINUTE">Menit</option> -->
                </select>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Toleransi <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="number" name="Toleransi" id="Toleransi" required="" placeholder="Toleransi. *Menit" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Shift Ganti Hari ? <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="checkbox" name="xGantiHari" id="xGantiHari" class="form-control ">
                <input type="hidden" name="GantiHari" id="GantiHari" class="form-control ">
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
    var LocationID = $('#LocationID').val();
    $(document).ready(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Shift/Read",
        data: {'id':'', 'RecordOwnerID': RecordOwnerID,'LocationID':LocationID},
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
        }
      });
    });
    $('#xGantiHari').click(function() {
        // $("#txtAge").toggle(this.checked);
        // console.log($('#xGantiHari').val());
        if ($('#xGantiHari').is(':checked')) {
          $('#GantiHari').val("1");
        }
        else{
          $('#GantiHari').val("0");
        }
    });

    $('#post_').submit(function (e) {
      $('#btn_Save').text('Tunggu Sebentar.....');
      $('#btn_Save').attr('disabled',true);

      e.preventDefault();
      var me = $(this);

      $.ajax({
            type    :'post',
            url     : '<?=base_url()?>C_Shift/CRUD',
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
    
    function GetData(id) {
      var where_field = 'id';
      var where_value = id;
      var table = 'users';
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Shift/Read",
        data: {'id':id, 'RecordOwnerID':RecordOwnerID,'LocationID':LocationID},
        dataType: "json",
        success: function (response) {
          $.each(response.data,function (k,v) {
            // $('#KodePenyakit').val(v.KodePenyakit).change;
            $('#id').val(v.id);
            $('#NamaShift').val(v.NamaShift);
            $('#MulaiBekerja').val(v.MulaiBekerja.split(".")[0]);
            $('#SelesaiBekerja').val(v.SelesaiBekerja.split(".")[0]);
            $('#IntervalPatroli').val(v.IntervalPatroli);
            $('#IntervalType').val(v.IntervalType).change();
            $('#Toleransi').val(v.Toleransi);
            $('#LocationID').val(v.LocationID);

            // console.log(v.GantiHari)
            $( "#GantiHari").val(v.GantiHari);
            if (v.GantiHari == "1") {
              $( "#xGantiHari").prop('checked', true);
            }
            else{
              $( "#xGantiHari").prop('checked', false);
            }
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
                    caption: "#",
                    allowEditing:false,
                    visible:false
                },
                {
                    dataField: "NamaShift",
                    caption: "Shift",
                    allowEditing:false
                },
                {
                    dataField: "MulaiBekerja",
                    caption: "Mulai",
                    allowEditing:false
                },
                {
                    dataField: "SelesaiBekerja",
                    caption: "Selesai",
                    allowEditing:false
                },
            ],
            onEditingStart: function(e) {
                GetData(e.data.id);
            },
            onInitNewRow: function(e) {
                // logEvent("InitNewRow");
                $('#modal_').modal('show');
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
                      url     : '<?=base_url()?>C_Shift/CRUD',
                      data    : {'id':id,'formtype':'delete', 'RecordOwnerID':RecordOwnerID,'LocationID':LocationID},
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