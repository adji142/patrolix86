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
                <button class="btn btn-secondary" id="btPrint">Cetak Bukti</button>
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
              <div class="col-md-12 col-sm-12  form-group">
                <div class="dx-viewport demo-container">
                  <div id="data-grid-demo">
                    <div id="gridContainer_pembayaran">
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

<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true" id="modal_pembayaran">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Modal Pembayaran</h4>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-2 col-sm-12  form-group">
            No. Transaksi
            <input type="text" id="NoTransaksi" name="NoTransaksi" placeholder="<AUTO>" class="form-control" ReadOnly>
            <input type="hidden" id="NamaUser" name="NamaUser" placeholder="<AUTO>" class="form-control" value = "<?php echo $NamaUser ?>" readonly>
            <!-- <span class="tooltiptext">Tooltip text</span> -->
          </div>
          <div class="col-md-3 col-sm-12  form-group">
            Tgl. Transaksi
            <input type="date" id="TglTransaksi" name="TglTransaksi" class="form-control">
            <!-- <span class="tooltiptext">Tooltip text</span> -->
          </div>
          <div class="col-md-7 col-sm-12  form-group">
            Keterangan
            <input type="text" id="Keterangan" name="Keterangan" class="form-control">
            <!-- <span class="tooltiptext">Tooltip text</span> -->
          </div>
          <div class="col-md-12 col-sm-12  form-group">
            <div class="dx-viewport demo-container">
              <div id="data-grid-demo">
                <div id="gridContainer_tagihan">
                </div>
              </div>
            </div>
            <!-- <span class="tooltiptext">Tooltip text</span> -->
          </div>
        </div>
        <div class="item" form-group>
          <button class="btn btn-primary" id="btn_SavePembayaran" disabled="">Save</button>
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
  var xPembayaranCount = 0;
  var xNoTransaksi = "";
  $(function () {
    $("#TglTransaksi").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });
    $(document).ready(function () {
      ResetData();
      var where_field = '';
      var where_value = '';
      var table = 'users';

      $.ajax({
        async: false,
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
          var items_data = [];

          for (var i = 0; i < response.data.length; i++) {
            var arr ={
                NoTransaksi     : response.data[i].NoTransaksi,
                TglTagihan      : response.data[i].TglTagihan,
                NISSiswa        : response.data[i].NISSiswa,
                NamaSiswa       : response.data[i].NamaSiswa,
                KelasJurusan    : response.data[i].KelasJurusan,
                Note            : response.data[i].Note,
                TotalTagihan    : ThousandSparator(response.data[i].TotalTagihan)
            }
            items_data.push(arr);
          }
          bindGrid(items_data);
        }
      });
      setEnableCommand()
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
          var items_data = [];

          for (var i = 0; i < response.data.length; i++) {
            var arr ={
                NoTransaksi     : response.data[i].NoTransaksi,
                TglTagihan      : response.data[i].TglTagihan,
                NISSiswa        : response.data[i].NISSiswa,
                NamaSiswa       : response.data[i].NamaSiswa,
                KelasJurusan    : response.data[i].KelasJurusan,
                Note            : response.data[i].Note,
                TotalTagihan    : ThousandSparator(response.data[i].TotalTagihan)
            }
            items_data.push(arr);
          }
          bindGrid(items_data);
        }
      });
    });
    $('#btn_SavePembayaran').click(function () {
        $('#btn_SavePembayaran').text('Tunggu Sebentar.....');
        $('#btn_SavePembayaran').attr('disabled',true);

        var dataGridDetail = $("#gridContainer_tagihan").dxDataGrid("instance");
        var dsDetail = dataGridDetail.getDataSource();
        var dataDetail = dsDetail.items();

        var errorCount =0;

        for (var i = 0 ; i < dataDetail.length; i++) {
          var tagihan = dataDetail[i].JumlahTagihan.replace(",","").substring(0,dataDetail[i].JumlahTagihan.replace(",","").length -3);
          if (dataDetail[i].JumlahPembayaran > tagihan) {
            errorCount += 1;
          }
        }

        if (errorCount > 0) {
          Swal.fire({
            type: 'error',
            title: 'Woops...',
            text: 'Jumlah Pembayaran Tidak boleh lebih dari Tagihan',
          });
          $('#modal_pembayaran').modal('show');
          $('#btn_SavePembayaran').text('Save');
          $('#btn_SavePembayaran').attr('disabled',false);
          // break;
        }
        else{
          var jsonParse = {
            "TglTransaksi"  : $('#TglTransaksi').val(),
            "Keterangan"    : $('#Keterangan').val(),
            "NamaUser"      : $('#NamaUser').val(),
            "ListDetail"    : dataDetail
          };

          // console.log("Execute");
          $.ajax({
            async: false,
            type: "post",
            url: "<?=base_url()?>C_TagihanSiswa/BayarTagihan",
            data: {'oParam':JSON.stringify(jsonParse)},
            dataType: "json",
            success: function (response) {
              // bindGridLookupSiswa(response.data);
              // $('#modal_Siswa').modal('show');

              if (response.success == true) {
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
                $('#modal_pembayaran').modal('toggle');
                Swal.fire({
                  type: 'error',
                  title: 'Woops...',
                  text: response.message,
                  // footer: '<a href>Why do I have this issue?</a>'
                }).then((result)=>{
                  $('#modal_pembayaran').modal('show');
                  $('#btn_SavePembayaran').text('Save');
                  $('#btn_SavePembayaran').attr('disabled',false);
                });
              }
            }
          });
        }
    });

    $('#btPrint').click(function(){
      window.open("<?php echo base_url()?>buktibayar/"+xNoTransaksi, "_blank");
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
                {
                  dataField : "TotalTagihan",
                  caption : "Total Tagihan",
                  allowEditing :false
                },
                {
                    dataField: "FileItem",
                    caption: "Action",
                    allowEditing:false,
                    cellTemplate: function(cellElement, cellInfo) {
                      LinkAccess = "";

                      if (cellInfo.data.TotalTagihan == 0) {
                        LinkAccess = "<button class='btn btn-primary StartPay' onClick =Pembayaran('"+cellInfo.data.NoTransaksi+"') disabled>Bayar</button>";
                      }
                      else{
                        LinkAccess = "<button class='btn btn-primary StartPay' onClick =Pembayaran('"+cellInfo.data.NoTransaksi+"')>Bayar</button>";
                      }
                      
                      // console.log();
                      cellElement.append(LinkAccess);
                  }
                }
            ],
            onSelectionChanged(selectedItems) {
              const data = selectedItems.selectedRowsData[0];
              if (data) {
                xNoTransaksi = data.NoTransaksi;
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

                    // Show Pembayaran
                    $.ajax({
                      type: "post",
                      url: "<?=base_url()?>C_TagihanSiswa/ReadPembayaran",
                      async: false,
                      data: {
                        'NoTransaksi':data.NoTransaksi
                      },
                      dataType: "json",
                      success: function (responseBayar) {
                        var items_bayar = [];

                        xPembayaranCount = responseBayar.data.length;

                        for (var i = 0; i < responseBayar.data.length; i++) {
                          var arr ={
                              NoTransaksi     : responseBayar.data[i].NoTransaksi,
                              TglTransaksi    : responseBayar.data[i].TglTransaksi,
                              Keterangan      : responseBayar.data[i].Keterangan,
                              JumlahPembayaran          : ThousandSparator(responseBayar.data[i].Jumlah)
                          }
                          items_bayar.push(arr);
                        }
                        bindGridPembayaran(items_bayar);
                        setEnableCommand();
                      }
                    });
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
            onSelectionChanged(selectedItems) {
              const data = selectedItems.selectedRowsData[0];
              if (data) {
                
              }
            },
        });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }

    function bindGridPembayaran(data) {

      $("#gridContainer_pembayaran").dxDataGrid({
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
                    dataField: "NoTransaksi",
                    caption: "No. Pembayran",
                    allowEditing:false
                },
                {
                    dataField: "TglTransaksi",
                    caption: "Tgl. Pembayaran",
                    allowEditing:false
                },
                {
                    dataField: "Keterangan",
                    caption: "Keterangan",
                    allowEditing:false
                },
                {
                    dataField: "JumlahPembayaran",
                    caption: "Jumlah Pembayaran",
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

      var today = now.getFullYear()+"-01-01";
      var lastDayofYear = now.getFullYear()+"-12-31";

      $('#FromDate').val(today);
      $('#ToDate').val(lastDayofYear);
    }
    
  });
  function Pembayaran(NoTransaksi) {
    $.ajax({
      type: "post",
      url: "<?=base_url()?>C_TagihanSiswa/ReadDetail",
      async: false,
      data: {
        'NoTransaksi':NoTransaksi
      },
      dataType: "json",
      success: function (response) {
        var items_data = [];

        for (var i = 0; i < response.data.length; i++) {
          var arr ={
              KodeItemTagihan     : response.data[i].KodeItemTagihan,
              NamaItemTagihan     : response.data[i].NamaItemTagihan,
              JumlahTagihan       : ThousandSparator(response.data[i].OutStanding),
              JumlahPembayaran    : 0,
              BaseNum             : response.data[i].NoTransaksi,
              BaseLine            : response.data[i].LineNumber
          }
          items_data.push(arr);
        }
        bindGridLookupTagihan(items_data);
        $('#modal_pembayaran').modal('show');
      }
    });
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

  function bindGridLookupTagihan(data) {

    $("#gridContainer_tagihan").dxDataGrid({
      allowColumnResizing: true,
          dataSource: data,
          keyExpr: "KodeItemTagihan",
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
                  dataField: "KodeItemTagihan",
                  caption: "KodeItemTagihan",
                  allowEditing:false,
                  visible:false
              },
              {
                  dataField: "NamaItemTagihan",
                  caption: "Nama Tagihan",
                  allowEditing:false
              },
              {
                  dataField: "JumlahTagihan",
                  caption: "Jumlah Tagihan",
                  allowEditing:false
              },
              {
                  dataField: "JumlahPembayaran",
                  caption: "Jumlah Pembayaran",
                  allowEditing:true,
                  alignment: "right",
              },
              {
                  dataField: "BaseNum",
                  caption: "BaseNum",
                  allowEditing:false,
                  visible:false
              },
              {
                  dataField: "BaseLine",
                  caption: "BaseLine",
                  allowEditing:false,
                  visible:false
              },
          ],
          onRowClick: function (rowInfo) {  
            rowInfo.component.editRow(rowInfo.rowIndex);  
          },
      });

      // add dx-toolbar-after
      // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
  }
  function setEnableCommand() {

    if ($('#TglTransaksi').val() != "" ) {
      $('#btn_SavePembayaran').prop('disabled', false)
    }
    else{
      $('#btn_SavePembayaran').prop('disabled', true)
    }

    if (xPembayaranCount > 0) {
      $('#btPrint').prop('disabled', false);
    }else{
      $('#btPrint').prop('disabled', true);
    }
  }
</script>