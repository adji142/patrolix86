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
            <h2>Laporan Keuangan</h2>
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
              <!-- <div class="col-md-3 col-sm-12  form-group">
                Jenis Laporan
                <select id="JenisLaporan" name="JenisLaporan" class="form-control">
                  <option value="">Pilih Jenis Laporan</option>
                  <option value="1">Per Siswa</option>
                  <option value="2">Per Kelas</option>
                  <option value="3">Global</option>
                </select>
              </div> -->
              <div class="col-md-3 col-sm-12  form-group">
                <!-- <input type="text" placeholder="NIK / Nama" class="form-control"> -->
                <br>
                <button class="btn btn-primary" id="btSearch">Cari Data</button>
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
      ResetData()
    });
    $('#btSearch').click(function () {
      var FromDate = $('#FromDate').val();
      var ToDate = $('#ToDate').val();
      window.open("<?php echo base_url()?>rptkeuangan/"+FromDate+','+ToDate, "_blank");
    });

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
  });
</script>