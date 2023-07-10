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
            <h2>Tambah Tagihan</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            <div class="row">
              <div class="col-md-2 col-sm-12  form-group">
                No. Transaksi
                <input type="text" id="NoTransaksi" name="NoTransaksi" placeholder="<AUTO>" class="form-control" ReadOnly>
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
              <div class="col-md-4 col-sm-12  form-group">
                Periode Tagihan
                <input type="month" id="PeriodFrom" name="PeriodFrom" class="form-control">
                <!-- <span class="tooltiptext">Tooltip text</span> -->
              </div>
              <div class="col-md-4 col-sm-12  form-group">
                Periode Selesai Tagihan
                <input type="month" id="PeriodTo" name="PeriodTo" class="form-control">
                <!-- <span class="tooltiptext">Tooltip text</span> -->
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                
              </div>
              <div class="col-md-3 col-sm-12  form-group">
                Mulai Tahun Ajaran
                <input type="text" id="TahunAjaranFrom" name="TahunAjaranFrom" class="form-control" maxlength="4">
                <!-- <span class="tooltiptext">Tooltip text</span> -->
              </div>
              <div class="col-md-3 col-sm-12  form-group">
                Selesai Tahun Ajaran
                <input type="text" id="TahunAjaranTo" name="TahunAjaranTo" class="form-control" maxlength="4">
                <!-- <span class="tooltiptext">Tooltip text</span> -->
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                <h3><center>Detail Tagihan</center></h3>
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                <div class="dx-viewport demo-container">
                  <div id="data-grid-demo">
                    <div id="gridContainerDetail">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                <h3><center>Tujuan Tagihan</center></h3>
                <button class="btn btn-primary" id="btAddSiswa">Tambah Siswa</button>
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                <div class="dx-viewport demo-container">
                  <div id="data-grid-demo">
                    <div id="gridContainerSiswa">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12 col-sm-12  form-group">
                <button class="btn btn-danger" id="btSaveTagihan" disabled="">Simpan</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /page content -->

<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true" id="modal_ItemTagihan">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Modal Item Tagihan</h4>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="dx-viewport demo-container">
          <div id="data-grid-demo">
            <div id="gridLookupItemTagihan">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary"  id="btSave">Save</button>
        
      </div>

    </div>
  </div>
</div>

<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true" id="modal_Siswa">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Modal Siswa</h4>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="dx-viewport demo-container">
          <div id="data-grid-demo">
            <div id="gridLookupSiswa">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary"  id="btSaveSiswa">Save</button>
        
      </div>

    </div>
  </div>
</div>
<?php
  require_once(APPPATH."views/parts/Footer.php");
?>
<script type="text/javascript">
  $(function () {
    var items_Detail = [];
    var items_Siswa = [];
    var tempArrSiswa = [];

    var arrSiswa ={
        NIS       : "",
        NamaSiswa : "",
        Kelas     : "",
    }

    var arrDetail ={
        KodeTagihan : "",
        NamaTagihan : "",
        Jumlah      : 0,
    }

    var JumlahTagihan = 0;

    // Validateion
    $("#TglTransaksi").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });

    $("#PeriodFrom").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });

    $("#PeriodTo").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });

    $("#TahunAjaranFrom").on('change keyup paste', function() {
        // console.log('I am pretty sure the text box changed');
        setEnableCommand()
    });

    $("#TahunAjaranTo").on('change keyup paste', function() {
        // console.log('I am /pretty sure the text box changed');
        setEnableCommand()
    });
    // Validateion
    $(document).ready(function () {
      bindGridSiswa(arrSiswa);
      bindGridDetail(arrDetail);

      $('#NoTransaksi').tooltip({'trigger':'hover', 'title': 'Nomor Transaksi'});
      $('#TglTransaksi').tooltip({'trigger':'hover', 'title': 'Tanggal Transaksi'});
    });
    $('#post_').submit(function (e) {
      $('#btn_Save').text('Tunggu Sebentar.....');
      $('#btn_Save').attr('disabled',true);

      e.preventDefault();
      var me = $(this);

      $.ajax({
        type    :'post',
        url     : '<?=base_url()?>C_JenisTagihan/CRUD',
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

    $('#btSave').click(function () {

      var result = items_Detail.find(item => item.KodeTagihan === arrDetail['KodeTagihan']);

      // console.log(result);
      if (typeof result == 'undefined') {
        items_Detail.push(arrDetail);
        bindGridDetail(items_Detail);
      }

      $('#modal_ItemTagihan').modal('toggle');

      var button = $('.dx-link-edit');
      button.click();

      setEnableCommand();
    });

    $('#btAddSiswa').click(function () {
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_Siswa/Read",
        data: {'NIS':''},
        dataType: "json",
        success: function (response) {
          bindGridLookupSiswa(response.data);
          $('#modal_Siswa').modal('show');
        }
      });
    });

    $('#btSaveSiswa').click(function () {
      // console.log(items_Siswa);
      bindGridSiswa(items_Siswa);
      $('#modal_Siswa').modal('toggle');
      setEnableCommand();
    });
    $('#btSaveTagihan').click(function () {
      // setEnableCommand();
      // Validasi
      $('#btSaveTagihan').text('Tunggu Sebentar ...');
      $('#btSaveTagihan').attr('disabled',true);

      var dataGridDetail = $("#gridContainerDetail").dxDataGrid("instance");
      var dsDetail = dataGridDetail.getDataSource();
      var dataDetail = dsDetail.items();

      var listDataDetail = [];
      var errorCount = 0;
      var errorMessage = "";

      // console.log(dsDetail);
      for (var i = 0 ; i < dataDetail.length; i++) {
        // Things[i]
        // console.log(dataDetail[i]);
        if (dataDetail[i].Jumlah === 0) {

          errorCount += 1;
          errorMessage = "Jumlah Tagihan tidak boleh 0";
          break;
        }
        else{
          arrDetail ={
              KodeTagihan : dataDetail[i].KodeTagihan,
              NamaTagihan : dataDetail[i].NamaTagihan,
              Jumlah      : dataDetail[i].Jumlah,
          }

          listDataDetail.push(arrDetail);
        }
      }

      if (errorCount > 0) {
        Swal.fire({
          type: 'error',
          title: 'Woops...',
          text: errorMessage,
        });
        $('#btSaveTagihan').text('Save');
        $('#btSaveTagihan').attr('disabled',false);
      }
      else{
        var jsonParse = {
          "TglTransaksi"  : $('#TglTransaksi').val(),
          "MulaiTagih"    : $("#PeriodFrom").val(),
          "SelesaiTagih"  : $("#PeriodTo").val(),
          "TahunAjaran"   : $('#TahunAjaranFrom').val().toString() + "-" + $('#TahunAjaranTo').val().toString(),
          "Keterangan"    : $('#Keterangan').val(),
          "ListSiswa"     : items_Siswa,
          "ListDetail"    : listDataDetail
        };

        // console.log(jsonParse);


        $.ajax({
          async: false,
          type: "post",
          url: "<?=base_url()?>C_TagihanSiswa/AddMassal",
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
              Swal.fire({
                type: 'error',
                title: 'Woops...',
                text: response.message,
                // footer: '<a href>Why do I have this issue?</a>'
              }).then((result)=>{
                $('#btSaveTagihan').text('Save');
                $('#btSaveTagihan').attr('disabled',false);
              })
            }
          }
        });
      }

      // {"TglTransaksi":"2023-06-15","MulaiTagih":"2023-07-01","SelesaiTagih":"2024-06-01","TahunAjaran":"2022-2023","Keterangan":"","ListSiswa":[{"NIS" : "99999999","NamaSiswa" : "Prasetyo Aji Wibowo"},{"NIS" : "88888888","NamaSiswa" : "Puspitasari"}],"ListDetail":[{"KodeTagihan" : "1","NamaTagihan" : "123","Jumlah":500000}]}

    })
    function bindGridLookupTagihan(data) {

      $("#gridLookupItemTagihan").dxDataGrid({
        allowColumnResizing: true,
        dataSource: data,
        keyExpr: "KodeTagihan",
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
        searchPanel: {
            visible: true,
            width: 240,
            placeholder: "Search..."
        },
        selection: {
          mode: 'single',
          // mode: 'multiple',
        },
        columns: [
          {
              dataField: "KodeTagihan",
              caption: "Kode Tagihan",
              allowEditing:false
          },
          {
              dataField: "NamaTagihan",
              caption: "Nama Jenis Tagihan",
              allowEditing:false
          },
        ],
        onSelectionChanged(selectedItems) {
          const data = selectedItems.selectedRowsData[0];
          if (data) {
            arrDetail ={
                KodeTagihan : data.KodeTagihan,
                NamaTagihan : data.NamaTagihan,
                Jumlah      : 0,
            }
          }
        }
      });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }

    function bindGridLookupSiswa(data) {

      $("#gridLookupSiswa").dxDataGrid({
        allowColumnResizing: true,
        dataSource: data,
        keyExpr: "NIS",
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
        searchPanel: {
            visible: true,
            width: 240,
            placeholder: "Search..."
        },
        selection: {
          mode: 'multiple',
        },
        columns: [
          {
              dataField: "NIS",
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
        ],
        onSelectionChanged(selectedItems){
          const data = selectedItems.selectedRowsData;
          if (data.length > 0) {
            items_Siswa = data;
            // $.each(data,function (k,v) {
            //   console.log(v.NIS);   
            // });
            // console.log(data);
          }
        }
      });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }

    function bindGridSiswa(data) {

      $("#gridContainerSiswa").dxDataGrid({
        allowColumnResizing: true,
        dataSource: data,
        keyExpr: "NIS",
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
            allowAdding:false,
            allowUpdating: false,
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
            enabled: false,
            fileName: "Daftar Jenis Tagihan"
        },
        columns: [
            {
                dataField: "NIS",
                caption: "No. Induk Siswa",
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
        ],
        onRowRemoved() {
          var dataGridDetail = $("#gridContainerSiswa").dxDataGrid("instance");
          var dsDetail = dataGridDetail.getDataSource();
          var dataDetail = dsDetail.items();
          // console.log(dataDetail);
          items_Siswa = dataDetail

          setEnableCommand();
        },
      });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }

    function bindGridDetail(data) {

      $("#gridContainerDetail").dxDataGrid({
        allowColumnResizing: true,
        dataSource: data,
        keyExpr: "KodeTagihan",
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
            visible: false,
            width: 240,
            placeholder: "Search..."
        },
        export: {
            enabled: false,
            fileName: "Daftar Jenis Tagihan"
        },
        columns: [
            {
                dataField: "KodeTagihan",
                caption: "Kode Tagihan",
                allowEditing:false
            },
            {
                dataField: "NamaTagihan",
                caption: "Nama Tagihan",
                allowEditing:false
            },
            {
                dataField: "Jumlah",
                caption: "Jumlah Tagihan",
                allowEditing:true
            },
        ],
        onInitNewRow: function(e) {
            // logEvent("InitNewRow");
          $.ajax({
            type: "post",
            url: "<?=base_url()?>C_JenisTagihan/Read",
            data: {'id':''},
            dataType: "json",
            success: function (response) {
              bindGridLookupTagihan(response.data);
              $('#modal_ItemTagihan').modal('show');
            }
          });
        },
        onRowClick: function (rowInfo) {  
          rowInfo.component.editRow(rowInfo.rowIndex);  
        },
        onRowUpdating: function(e) {
          // console.log("Updated")
          setEnableCommand()
        },
    });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }

    function setEnableCommand() {
      var dataGridDetail = $("#gridContainerDetail").dxDataGrid("instance");
      var dsDetail = dataGridDetail.getDataSource();
      var dataDetail = dsDetail.items();
      // console.log(ds.items());

      // console.log($('#TglTransaksi').val());
      // console.log($('#PeriodFrom').val());
      // console.log($('#PeriodTo').val());
      // console.log($('#TahunAjaranFrom').val());
      // console.log($('#TahunAjaranTo').val());
      // // console.log(JumlahTagihan);
      // console.log(items_Siswa.length);

      if ($('#TglTransaksi').val() != "" && $('#PeriodFrom').val() != "" && $('#PeriodTo').val() != "" && $('#TahunAjaranFrom').val() != "" && $('#TahunAjaranTo').val() != ""&& items_Siswa.length > 0) {
        $('#btSaveTagihan').prop('disabled', false)
      }
      else{
        $('#btSaveTagihan').prop('disabled', true)
      }

    }
  });
</script>