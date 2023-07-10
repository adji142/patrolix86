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
            <h2>Tagihan Siswa</h2>
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
                Jurusan
                <select id="Jurusan" name="Jurusan" class="form-control">
                  <option value="">Pilih Jurusan..</option>
                  <?php
                    $rs = $this->ModelsExecuteMaster->GetData('tjurusan')->result();

                    foreach ($rs as $key) {
                      echo "<option value = '".$key->id."'>".$key->NamaJurusan."</option>";
                    }
                  ?>
                </select>
              </div>

              <div class="col-md-3 col-sm-12  form-group">
                Kelas
                <select id="Kelas" name="Kelas" class="form-control">
                  <option value="">Pilih Kelas..</option>
                  <?php
                    $rs = $this->ModelsExecuteMaster->GetData('tkelas')->result();

                    foreach ($rs as $key) {
                      echo "<option value = '".$key->id."'>".$key->NamaKelas."</option>";
                    }
                  ?>
                </select>
              </div>

              <div class="col-md-2 col-sm-12  form-group">
                Status Document
                <select id="Status" name="Status" class="form-control">
                  <option value="Open">Belum Lunas</option>
                  <option value="Close">Lunas</option>
                  <option value="">Semua</option>
                </select>
              </div>

              <div class="col-md-4 col-sm-12  form-group">
                <!-- <input type="text" placeholder="NIK / Nama" class="form-control"> -->
                <br>
                <button class="btn btn-primary" id="btSearch">Cari Data</button>
                <a class="btn btn-danger" href="<?php echo base_url().'addtagihan' ?>">Tambah Data</a>
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                <div class="dx-viewport demo-container">
                  <div id="data-grid-demo">
                    <div id="gridContainer">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                <div class="dx-viewport demo-container">
                  <div id="data-grid-demo">
                    <div id="gridContainer_detail">
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
    $(document).ready(function () {
      ResetData();
      var where_field = '';
      var where_value = '';
      var table = 'users';

      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_TagihanSiswa/ReadHeader",
        data: {
          'FromDate':$('#FromDate').val(),
          'ToDate':$('#ToDate').val(),
          'Kelas':$('#Kelas').val(),
          'Jurusan':$('#Jurusan').val(),
          'Status':$('#Status').val(),
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
        url: "<?=base_url()?>C_TagihanSiswa/ReadHeader",
        data: {
          'FromDate':$('#FromDate').val(),
          'ToDate':$('#ToDate').val(),
          'Kelas':$('#Kelas').val(),
          'Jurusan':$('#Jurusan').val(),
          'Status':$('#Status').val(),
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
            keyExpr: "NoTransaksi",
            showBorders: true,
            allowColumnReordering: true,
            allowColumnResizing: true,
            columnAutoWidth: true,
            hoverStateEnabled: true,
            paging: {
              pageSize: 10,
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
                enabled: false,
                fileName: "Daftar Jenis Tagihan"
            },
            selection: {
              mode: 'single',
            },
            columns: [
                {
                    dataField: "NoTransaksi",
                    caption: "No. Tagihan",
                    allowEditing:false
                },
                {
                    dataField: "TglTagihan",
                    caption: "Tgl. Tagihan",
                    allowEditing:false
                },
                {
                    dataField: "NISSiswa",
                    caption: "NIS",
                    allowEditing:false
                },
                {
                    dataField: "NamaSiswa",
                    caption: "Nama Siswa",
                    allowEditing:false
                },
                {
                    dataField: "KelasJurusan",
                    caption: "Kelas/Jurusan",
                    allowEditing:false
                },
                {
                    dataField: "Note",
                    caption: "Note",
                    allowEditing:false
                },
            ],
            onSelectionChanged(selectedItems) {
              const data = selectedItems.selectedRowsData[0];
              if (data) {
                $.ajax({
                  type: "post",
                  url: "<?=base_url()?>C_TagihanSiswa/ReadDetail",
                  async: false,
                  data: {
                    'NoTransaksi':data.NoTransaksi
                  },
                  dataType: "json",
                  success: function (response) {
                    var items_data = [];

                    for (var i = 0; i < response.data.length; i++) {
                      var arr ={
                          NoTransaksi     : response.data[i].NoTransaksi,
                          LineNumber      : response.data[i].LineNumber,
                          KodeItemTagihan : response.data[i].KodeItemTagihan,
                          NamaItemTagihan : response.data[i].NamaItemTagihan,
                          JumlahTagihan   : ThousandSparator(response.data[i].JumlahTagihan)
                      }
                      items_data.push(arr);
                    }
                    bindGridDetail(items_data);
                  }
                });
              }
            },
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
</script>