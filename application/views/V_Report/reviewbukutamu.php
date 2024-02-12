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
            <h2>Review Buku Tamu</h2>
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
                <br>
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
        url: "<?=base_url()?>C_GuestLog/Read",
        data: {
          'TglAwal'       :$('#TglAwal').val(),
          'TglAkhir'      :$('#TglAkhir').val(),
          'RecordOwnerID' :RecordOwnerID,
          'KodeLokasi'    :$('#LocationID').val(),
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
        url: "<?=base_url()?>C_GuestLog/Read",
        data: {
          'TglAwal'       :$('#TglAwal').val(),
          'TglAkhir'      :$('#TglAkhir').val(),
          'RecordOwnerID' :RecordOwnerID,
          'KodeLokasi'    :$('#LocationID').val(),
        },
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
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
                fileName: "Daftar Bukutamu"
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
                    dataField: "TglMasuk",
                    caption: "Masuk",
                    allowEditing:false,
                },
                {
                    dataField: "TglKeluar",
                    caption: "Keluar",
                    allowEditing:false,
                },
                {
                    dataField: "NamaTamu",
                    caption: "Nama Tamu",
                    allowEditing:false,
                },
                {
                    dataField: "NamaYangDicari",
                    caption: "Nama Yang Dicari",
                    allowEditing:false,
                },
                {
                    dataField: "Tujuan",
                    caption: "Tujuan Berkunjung",
                    allowEditing:false,
                },
                {
                    dataField: "Keterangan",
                    caption: "Keterangan",
                    allowEditing:false
                }
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
</script>