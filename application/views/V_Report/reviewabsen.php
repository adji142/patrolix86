<?php
    require_once(APPPATH."views/parts/Header.php");
    require_once(APPPATH."views/parts/Sidebar.php");
    $active = 'dashboard';
?>
<!-- <style type="text/css">
  a:link, a:visited {
    color: white;
  }
  .over{
    color: red;
  }
</style> -->
<!-- page content -->
<div class="right_col" role="main">
  <div class="">

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12  ">
        <div class="x_panel">
          <div class="x_title">
            <h2>Review Patroli</h2>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            <div class="row">
              <div class="col-md-3 col-sm-12  form-group">
                Tgl Awal
                <input type="date" id="TglAwal" name="TglAwal" class="form-control">
              </div>
              <div class="col-md-3 col-sm-12  form-group">
                Tgl Akhir
                <input type="date" id="TglAkhir" name="TglAkhir" class="form-control">
              </div>

              <div class="col-md-3 col-sm-12  form-group">
                Lokasi Patroli
                <select id="LocationID" name="LocationID" class="form-control">

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

              <div class="col-md-3 col-sm-12  form-group">
                Security
                <select id="KodeKaryawan" name="KodeKaryawan" class="form-control">
                  <option value="">Pilih Security..</option>
                </select>
              </div>

              <div class="col-md-3 col-sm-12  form-group">
                <button class="btn btn-success" id="btSearch">Proses</button>
              </div>

              <div class="col-md-12 col-sm-12  form-group">
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

<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true" id="modal_detail_absen">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Detail Absensi</h4>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 col-sm-12 ">
            <table class="table">
              <tr>
                <td>NIK </td>
                <td>:</td>
                <td><div id="NIK_Detail"></div></td>
              </tr>
              <tr>
                <td>Nama </td>
                <td>:</td>
                <td><div id="Nama_Detail"></div></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6 col-sm-6 ">
            <center>CHECK IN
              <div id="image_result_IN"></div>
            </center> <br>
            <table class="table">
              <tr>
                <td>Tanggal </td>
                <td>:</td>
                <td><div id="Tanggal_Detail_IN"></div></td>
              </tr>
              <tr>
                <td>Jam </td>
                <td>:</td>
                <td><div id="Jam_Detail_IN"></div></td>
              </tr>
              <tr>
                <td>Lokasi </td>
                <td>:</td>
                <td><div id="Lokasi_Detail_IN"></div></td>
              </tr>
              <tr>
                <td>Shift </td>
                <td>:</td>
                <td><div id="Shift_Detail_IN"></div></td>
              </tr>
            </table>
          </div>

          <div class="col-md-6 col-sm-6 ">
            <center>CHECK OUT
              <div id="image_result_OUT"></div>
            </center><br>
            <table class="table">
              <tr>
                <td>Tanggal </td>
                <td>:</td>
                <td><div id="Tanggal_Detail_OUT"></div></td>
              </tr>
              <tr>
                <td>Jam </td>
                <td>:</td>
                <td><div id="Jam_Detail_OUT"></div></td>
              </tr>
              <tr>
                <td>Lokasi </td>
                <td>:</td>
                <td>
                  <div id="Lokasi_Detail_OUT"></div>
                </td>
              </tr>
              <tr>
                <td>Shift </td>
                <td>:</td>
                <td><div id="Shift_Detail_OUT"></div></td>
              </tr>
            </table>
          </div>
          
        </div>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        
      </div> -->

    </div>
  </div>
</div>
<!-- /page content -->
<?php
  require_once(APPPATH."views/parts/Footer.php");
?>
<script type="text/javascript">
  $(function () {
    var RecordOwnerID = "<?php echo $this->session->userdata('RecordOwnerID') ?>";
    $(document).ready(function () {

      var now = new Date();

      var day = ("0" + now.getDate()).slice(-2);
      var month = ("0" + (now.getMonth() + 1)).slice(-2);

      var today = now.getFullYear()+"-"+month+"-01";
      var lastDayofYear = now.getFullYear()+"-"+month+"-"+day;

      $('#TglAwal').val(today);
      $('#TglAkhir').val(lastDayofYear);

      ResetData();
      var where_field = '';
      var where_value = '';
      var table = 'users';

      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Absensi/ReadReport",
        data: {
          'TglAwal'       :$('#TglAwal').val(),
          'TglAkhir'      :$('#TglAkhir').val(),
          'RecordOwnerID' :RecordOwnerID,
          'KodeKaryawan'  :$('#KodeKaryawan').val(),
          'KodeLokasi'    :$('#KodeLokasi').val(),
        },
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
        }
      });
    });
    $('.close').click(function() {
      location.reload();
    });
    $('#btSearch').click(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Absensi/ReadReport",
        data: {
          'TglAwal'       :$('#TglAwal').val(),
          'TglAkhir'      :$('#TglAkhir').val(),
          'RecordOwnerID' :RecordOwnerID,
          'KodeKaryawan'  :$('#KodeKaryawan').val(),
          'LocationID'    :$('#LocationID').val(),
        },
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
        url: "<?=base_url()?>C_Security/Read",
        data: {
          'RecordOwnerID' :RecordOwnerID,
          'LocationID'    :$('#LocationID').val()
        },
        dataType: "json",
        success: function (response) {
          // bindGrid(response.data);
          $('#KodeKaryawan').empty();
          if (response.data.length > 0) {
            $('#KodeKaryawan').append('<option value="">Pilih Security</option>');
            $.each(response.data,function (k,v) {
              $('#KodeKaryawan').append('<option value="' + v.NIK + '">' + v.NamaSecurity + '</option>');
            });
          }
          else{
            $('#KodeKaryawan').append('<option value="">Pilih Security</option>');
          }
        }
      });
    });

    function bindGrid(data) {

      $("#gridContainer").dxDataGrid({
        allowColumnResizing: true,
            dataSource: data,
            keyExpr: "id",
            showBorders: true,
            allowColumnReordering: true,
            allowColumnResizing: true,
            columnAutoWidth: true,
            hoverStateEnabled: true,
            paging: {
              pageSize: 50,
              enabled: true
            },
            pager: {
              visible: true,
              allowedPageSizes: [5, 10, 'all'],
              showPageSizeSelector: true,
              showInfo: true,
              showNavigationButtons: true,
            },
            editing: {
                mode: "row",
                allowAdding:false,
                allowUpdating: false,
                allowDeleting: false,
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
                fileName: "Daftar Absensi"
            },
            selection: {
              mode: 'single',
            },
            columns: [
                {
                    dataField: "Tanggal",
                    caption: "Tanggal",
                    allowEditing:false,
                },
                {
                    dataField: "KodeKaryawan",
                    caption: "Kode Security",
                    allowEditing:false,
                },
                {
                    dataField: "NamaSecurity",
                    caption: "Nama Security",
                    allowEditing:false,
                },
                {
                    dataField: "NamaArea",
                    caption: "Lokasi Project",
                    allowEditing:false,
                },
                {
                    dataField: "Checkin",
                    caption: "Checkin",
                    allowEditing:false,
                },
                {
                    dataField: "CheckOut",
                    caption: "CheckOut",
                    allowEditing:false
                },
                {
                    dataField: "id",
                    caption: "#",
                    allowEditing:false,
                    visible: false,
                },
                {
                    dataField: "FileItem",
                    caption: "Action",
                    allowEditing:false,
                    cellTemplate: function(cellElement, cellInfo) {
                      LinkAccess = "<button class='btn btn-info' onClick=loadDetail('"+cellInfo.data.id+"')>Lihat Detail</button>";
                      cellElement.append(LinkAccess);
                  }
                }
            ],
            // onCellPrepared: function (cellInfo) {
            //   if (cellInfo.rowType === "data") {
            //     // console.log(cellInfo);
            //     // var tgl = cellInfo.data.TanggalPatroli.split("/");
            //     // console.log(parseInt(tgl[1]));
            //     const date = new Date();

            //     let day = date.getDate();
            //     let month = date.getMonth();
            //     let year = date.getFullYear();

            //     let currentDate = day+'-'+month+'-'+year;
            //     const xJadwalPatroli = cellInfo.data.JamJadwal.split(":");
            //     const xToleransi = cellInfo.data.Toleransi.split(":");
            //     const xJamAktual = cellInfo.data.JamAktual.split(":");

            //     // console.log(currentDate + ' ' +xJadwalPatroli);

            //     const JadwalPatroli = new Date(+year,+month,+day,+xJadwalPatroli[0],+xJadwalPatroli[1],+xJadwalPatroli[2]);
            //     const Toleransi =  new Date(+year,+month,+day,+xToleransi[0],+xToleransi[1],+xToleransi[2]);
            //     const JamAktual = new Date(+year,+month,+day,+xJamAktual[0],+xJamAktual[1],+xJamAktual[2]);

            //     JadwalPatroli.setMinutes(JadwalPatroli.getMinutes() + xToleransi[1]);

            //     var fixJadwal = new Date(JadwalPatroli);

            //     // console.log(JadwalPatroli)

            //     // cellInfo.cellElement.css("backgroundColor", "red");

            //     if (JamAktual > fixJadwal) {
            //       cellInfo.cellElement.css("backgroundColor", "red");
            //       cellInfo.cellElement.css("color", "white");
            //     }


            //     // console.log(fixJadwal)
            //     // console.log(JadwalPatroli);
            //   }
            // }
        });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }

    function ResetData() {
      var now = new Date();

      var day = ("0" + now.getDate()).slice(-2);
      var month = ("0" + (now.getMonth() + 1)).slice(-2);

      var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
      var lastDayofYear = now.getFullYear()+"-12-31";

      $('#FromDate').val(today);
      $('#ToDate').val(lastDayofYear);
    }

    function ThousandSparator(nStr) {
      nStr += '';
      var x = nStr.split('.');
      var x1 = x[0];
      var x2 = x.length > 1 ? '.' + x[1] : '';
      var rgx = /(\d+)(\d{3})/;
      while (rgx.test(x1)) {
          x1 = x1.replace(rgx, '$1' + ',' + '$2');
      }
      return x1 + x2;
  }
  });
  function loadDetail(absentID) {
    var RecordOwnerID = "<?php echo $this->session->userdata('RecordOwnerID') ?>";
    // alert(absentID)
    $.ajax({
      type: "post",
      url: "<?=base_url()?>C_Absensi/ReadReport",
      data: {
        'id'            :absentID, 
        'RecordOwnerID' :RecordOwnerID,
        'TglAwal'       :$('#TglAwal').val(),
        'TglAkhir'      :$('#TglAkhir').val(),
      },
      dataType: "json",
      success: function (response) {
        $.each(response.data,function (k,v) {
          // $('#KodePenyakit').val(v.KodePenyakit).change;
          $('#NIK_Detail').text(v.KodeKaryawan);
          $('#Nama_Detail').text(v.NamaSecurity);

          var baseurl = "<?php echo base_url() ?>Assets/images/Absensi/";
          $('#image_result_IN').html("<img src ='"+baseurl+v.ImageIN+"' width = '50%'> ");
          $('#image_result_OUT').html("<img src ='"+baseurl+v.ImageOUT+"' width = '50%'> ");

          $('#Tanggal_Detail_IN').text(v.CheckinDate);
          $('#Jam_Detail_IN').text(v.CheckinTime);
          // $('#Lokasi_Detail_IN').text(v.KoordinatIN);
          $("#Lokasi_Detail_IN").append("<a href ='<?php echo base_url() ?>Assets/map.php?latlang="+v.KoordinatIN+"' target ='_blank' color='red'>"+v.KoordinatIN+"</a>");

          $('#Shift_Detail_IN').text(v.NamaShift);

          $('#Tanggal_Detail_OUT').text(v.CheckoutDate);
          $('#Jam_Detail_OUT').text(v.CheckoutTime);
          $("#Lokasi_Detail_OUT").append("<a href ='<?php echo base_url() ?>Assets/map.php?latlang="+v.KoordinatOUT+"' target ='_blank' color='red'>"+v.KoordinatOUT+"</a>");
          $('#Shift_Detail_OUT').text(v.NamaShift);

          $('#modal_detail_absen').modal('show');
        });
      }
    });
    // $('#modal_detail_absen').modal('show');
    // window.open("<?php echo base_url()?>Assets/map.php?latlang="+koordinat, "_blank");
  }
</script>