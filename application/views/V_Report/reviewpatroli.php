<?php
    require_once(APPPATH."views/parts/Header.php");
    require_once(APPPATH."views/parts/Sidebar.php");
    $active = 'dashboard';
?>
<style type="text/css">
  a:link, a:visited {
    color: white;
  }
  .over{
    color: red;
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
                <button class="btn btn-warning" id="btExportpdf">Export PDF</button>
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
        url: "<?=base_url()?>C_ReviewPatroli/Read",
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
    $('.close').click(function() {
      location.reload();
    });
    $('#btSearch').click(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_ReviewPatroli/Read",
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

    $('#btExportpdf').click(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_ReviewPatroli/generatePDF",
        data: {
          'TglAwal'       :$('#TglAwal').val(),
          'TglAkhir'      :$('#TglAkhir').val(),
          'RecordOwnerID' :RecordOwnerID,
          'KodeKaryawan'  :$('#KodeKaryawan').val(),
          'LocationID'    :$('#LocationID').val(),
        },
        dataType: "json",
        success: function (response) {
          // bindGrid(response.data);
          var linkurl = "<?php echo base_url() ?>Assets/doc/"+RecordOwnerID+".pdf";

          window.open(linkurl, '_blank');
        }
      });
    })

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
                fileName: "Daftar Patroli"
            },
            selection: {
              mode: 'single',
            },
            columns: [
                {
                    dataField: "NamaLokasi",
                    caption: "Lokasi",
                    allowEditing:false,
                    groupIndex:0
                },
                {
                    dataField: "NamaSecurity",
                    caption: "Security",
                    allowEditing:false,
                    groupIndex:1
                },
                {
                    dataField: "NamaCheckPoint",
                    caption: "Check Point",
                    allowEditing:false,
                    groupIndex:2
                },
                {
                    dataField: "NamaShift",
                    caption: "Shift",
                    allowEditing:false,
                    groupIndex:3
                },
                {
                    dataField: "id",
                    caption: "#",
                    allowEditing:false,
                    visible:false
                },
                {
                    dataField: "TanggalPatroli",
                    caption: "Tanggal",
                    allowEditing:false,
                    cssClass: "TglPatrol",
                },
                {
                    dataField: "JamJadwal",
                    caption: "Jam Jadwal Patroli",
                    allowEditing:false
                },
                {
                    dataField: "Toleransi",
                    caption: "Toleransi",
                    allowEditing:false
                },
                {
                    dataField: "JamAktual",
                    caption: "Jam Patroli",
                    allowEditing:false
                },
                {
                    dataField: "Catatan",
                    caption: "Catatan",
                    allowEditing:false
                },
                {
                    dataField: "Koordinat",
                    caption: "Koordinat",
                    allowEditing:false,
                    visible:false
                },
                {
                    dataField: "Image",
                    caption: "Image",
                    allowEditing:false,
                    visible:false
                },
                {
                    dataField: "FileItem",
                    caption: "Action",
                    allowEditing:false,
                    cellTemplate: function(cellElement, cellInfo) {
                      LinkAccess = "<a style href = '<?=base_url()?>Assets/images/patroli/"+cellInfo.data.Image+"' class='btn btn-info' target='_blank'>Lihat Gambar</a>";
                      LinkAccess += "<button class='btn btn-success' onClick=loadMap('"+cellInfo.data.Koordinat+"')>Lihat Lokasi</button>";
                      cellElement.append(LinkAccess);
                  }
                }
            ],
            onCellPrepared: function (cellInfo) {
              if (cellInfo.rowType === "data") {
                // console.log(cellInfo);
                // var tgl = cellInfo.data.TanggalPatroli.split("/");
                // console.log(parseInt(tgl[1]));
                const date = new Date();

                let day = date.getDate();
                let month = date.getMonth();
                let year = date.getFullYear();

                let currentDate = day+'-'+month+'-'+year;
                const xJadwalPatroli = cellInfo.data.JamJadwal.split(":");
                const xToleransi = cellInfo.data.Toleransi.split(":");
                const xJamAktual = cellInfo.data.JamAktual.split(":");

                // console.log(currentDate + ' ' +xJadwalPatroli);

                const JadwalPatroli = new Date(+year,+month,+day,+xJadwalPatroli[0],+xJadwalPatroli[1],+xJadwalPatroli[2]);
                const Toleransi =  new Date(+year,+month,+day,+xToleransi[0],+xToleransi[1],+xToleransi[2]);
                const JamAktual = new Date(+year,+month,+day,+xJamAktual[0],+xJamAktual[1],+xJamAktual[2]);

                JadwalPatroli.setMinutes(JadwalPatroli.getMinutes() + xToleransi[1]);

                var fixJadwal = new Date(JadwalPatroli);

                // console.log(JadwalPatroli)

                // cellInfo.cellElement.css("backgroundColor", "red");

                if (JamAktual > fixJadwal) {
                  cellInfo.cellElement.css("backgroundColor", "red");
                  cellInfo.cellElement.css("color", "white");
                }


                // console.log(fixJadwal)
                // console.log(JadwalPatroli);
              }
            }
        });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }

    function bindGridDetail(data) {

      $("#gridContainer_detail").dxDataGrid({
        allowColumnResizing: true,
            dataSource: data,
            keyExpr: "NoTransaksi",
            showBorders: true,
            allowColumnReordering: true,
            allowColumnResizing: true,
            columnAutoWidth: true,
            hoverStateEnabled: true,
            paging: {
                enabled: false
            },
            selection: {
              mode: 'single',
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
            columns: [
                {
                    dataField: "LineNumber",
                    caption: "RowID",
                    allowEditing:false
                },
                {
                    dataField: "KodeItemTagihan",
                    caption: "Kode Tagihan",
                    allowEditing:false
                },
                {
                    dataField: "NamaItemTagihan",
                    caption: "Nama Tagihan",
                    allowEditing:false
                },
                {
                    dataField: "JumlahTagihan",
                    caption: "Jumlah Tagihan",
                    allowEditing:false,
                    alignment: "right",
                },
            ],
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
  function loadMap(koordinat) {
    // alert(koordinat)
    window.open("<?php echo base_url()?>Assets/map.php?latlang="+koordinat, "_blank");
  }
</script>