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
            <h2>Buku Kas</h2>
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

              <div class="col-md-4 col-sm-12  form-group">
                <!-- <input type="text" placeholder="NIK / Nama" class="form-control"> -->
                <br>
                <button class="btn btn-primary" id="btSearch">Cari Data</button>
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

<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true" id="modal_">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Modal Tambah Mutasi Kas</h4>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="post_" data-parsley-validate class="form-horizontal form-label-left">
          <div class="item form-group">
            <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Jenis Mutasi <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 ">
              <select class="form-control " name="JenisMutasi" id="JenisMutasi">
                <option value="-1">Pilih Jenis Mutasi</option>
                <option value="1">Penerimaan</option>
                <option value="0">Pengeluaran</option>
              </select>
            </div>
          </div>

          <div class="item form-group">
            <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Pos Keuangan <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 ">
              <select class="form-control " name="KodeAkun" id="KodeAkun">
                <option value="">Pilih Pos Keuangan</option>
              </select>
            </div>
          </div>
          <div class="item form-group">
            <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Tanggal Transaksi <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 ">
              <input type="date" name="TglTransaksi" id="TglTransaksi" required="" class="date-picker form-control ">
            </div>
          </div>
          <div class="item form-group">
            <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Jumlah <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 ">
              <input type="number" name="Jumlah" id="Jumlah" required="" placeholder="Jumlah" class="form-control ">
              <input type="hidden" name="formtype" id="formtype" value="add">
            </div>
          </div>
          <div class="item form-group">
            <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Keterangan <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 ">
              <input type="text" name="Keterangan" id="Keterangan" required="" class="form-control ">
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
<!-- /page content -->
<?php
  require_once(APPPATH."views/parts/Footer.php");
?>
<script type="text/javascript">
  $(function () {
    $(document).ready(function () {
      setEnableCommand()
      ResetData()
      getData()
    });
    $('.close').click(function() {
      location.reload();
    });
    $('#btSearch').click(function () {
      getData();
    });

    $("#JenisMutasi").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });
    $("#KodeAkun").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });
    $("#TglTransaksi").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });
    $("#Jumlah").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });
    $('#JenisMutasi').change(function () {
      $.ajax({
        async:false,
        type: "post",
        url: "<?=base_url()?>C_PosKeuangan/Read",
        data: {
          'KodePosKeuangan' :'',
          'JenisPos'        :$('#JenisMutasi').val()
        },
        dataType: "json",
        success: function (response) {
          // bindGrid(response.data);
          $('#KodeAkun').empty();
          if (response.data.length > 0) {
            $('#KodeAkun').append('<option value="-1">Pilih Pos Keuangan</option>');
            $.each(response.data,function (k,v) {
              $('#KodeAkun').append('<option value="' + v.KodePosKeuangan + '">' + v.NamaPos + '</option>');
            });
          }
          else{
            $('#KodeAkun').append('<option value="-1">Pilih Pos Keuangan</option>');
          }
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
            url     : '<?=base_url()?>C_BukuKas/CRUD',
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
    function getData() {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_BukuKas/Read",
        data: {
          'FromDate':$('#FromDate').val(),
          'ToDate':$('#ToDate').val()
        },
        dataType: "json",
        success: function (response) {
          // bindGrid(response.data);
          // console.log(response.data);
          var items_data=[];
          var SaldoAwal = 0.0;
          var SaldoAkhir = 0.0;
          for (var i = 0; i < response.data.length; i++) {
            // Things[i]
            var arr = {};
            if (response.data[i]["KodeAkun"] == "99") {
              SaldoAwal = response.data[i]["SaldoAwal"];
              // SaldoAkhir += Number(SaldoAwal) + Number(response.data[i]["Pemasukan"]) - Number(response.data[i]["Pengeluaran"])
              arr = {
                KodeAkun      : response.data[i]["KodeAkun"],
                NamaPos       : response.data[i]["NamaPos"],
                Reff          : response.data[i]["Reff"],
                TglTransaksi  : "",
                Keterangan    : "",
                // SaldoAwal     : ThousandSparator(SaldoAwal),
                Pemasukan     : ThousandSparator(response.data[i]["Pemasukan"]),
                Pengeluaran   : ThousandSparator(response.data[i]["Pengeluaran"]),
                Saldo         : ThousandSparator(SaldoAwal)
              }
            }
            else{
              // console.log();
              SaldoAkhir += Number(SaldoAwal) + Number(response.data[i]["Pemasukan"]) - Number(response.data[i]["Pengeluaran"])
              arr = {
                KodeAkun      : response.data[i]["KodeAkun"],
                NamaPos       : response.data[i]["NamaPos"],
                Reff          : response.data[i]["Reff"],
                TglTransaksi  : response.data[i]["TglTransaksi"],
                Keterangan    : response.data[i]["Keterangan"],
                // SaldoAwal     : ThousandSparator(response.data[i]["SaldoAwal"]),
                Pemasukan     : ThousandSparator(response.data[i]["Pemasukan"]),
                Pengeluaran   : ThousandSparator(response.data[i]["Pengeluaran"]),
                Saldo         : ThousandSparator(SaldoAkhir)
              }
            }
            // console.log()
            items_data.push(arr);
          }
          // console.log(items_data);
          bindGrid(items_data);
        }
      });
    }
    function bindGrid(data) {

      $("#gridContainer").dxDataGrid({
        allowColumnResizing: true,
            dataSource: data,
            keyExpr: "KodeAkun",
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
                allowAdding:true,
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
                fileName: "Buku Kas"
            },
            selection: {
              mode: 'single',
            },
            columns: [
                {
                    dataField: "KodeAkun",
                    caption: "Kode Akun",
                    allowEditing:false,
                    visible:false
                },
                {
                    dataField: "NamaPos",
                    caption: "Nama Akun",
                    allowEditing:false,
                },
                {
                    dataField: "Reff",
                    caption: "Reff",
                    allowEditing:false
                },
                {
                    dataField: "TglTransaksi",
                    caption: "Tgl. Transaksi",
                    allowEditing:false
                },
                {
                    dataField: "Keterangan",
                    caption: "Keterangan",
                    allowEditing:false
                },
                // {
                //     dataField: "SaldoAwal",
                //     caption: "Saldo Awal",
                //     allowEditing:false
                // },
                {
                    dataField: "Pemasukan",
                    caption: "Pemasukan",
                    allowEditing:false
                },
                {
                    dataField: "Pengeluaran",
                    caption: "Pengeluaran",
                    allowEditing:false
                },
                {
                    dataField: "Saldo",
                    caption: "Saldo",
                    allowEditing:false
                },
            ],
            onInitNewRow: function(e) {
                // logEvent("InitNewRow");
                $('#modal_').modal('show');
            },
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
  function ResetData() {
    var now = new Date();

    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);

    var today = now.getFullYear()+"-"+month+"-01";
    var lastDayofYear = now.getFullYear()+"-"+month+"-"+day;

    $('#FromDate').val(today);
    $('#ToDate').val(lastDayofYear);
  }

  function setEnableCommand() {

      if ($('#JenisMutasi').val() != "-1" && $('#KodeAkun').val() != "-1" && $('#TglTransaksi').val() != "" && $('#Jumlah').val() != 0) {
        $('#btn_Save').prop('disabled', false)
      }
      else{
        $('#btn_Save').prop('disabled', true)
      }

    }
  });
</script>