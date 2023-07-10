<?php
    require_once(APPPATH."views/parts/Header.php");
    require_once(APPPATH."views/parts/Sidebar.php");
    $active = 'dashboard';
    // $this->load->helper("terbilang");
?>

<style type="text/css">
	#menu_toggle {
		display: none!important;
	}
	@media print {
	   .dx-button-content{
			display: none!important;
		}
	}
	
  }
</style>

<div class="right_col" role="main">
  <div class="">

    <div class="clearfix"></div>
    	<div class="row">
	        <div class="col-md-12">
	          	<div class="x_panel">
		            <div class="x_title">
		              <h2>Laporan Penerimaan Pembayaran Siswa <small>SMK Al-Islam Kalijambe</small></h2>
		              <div class="clearfix"></div>
		            </div>

		            <div class="x_content">
		            	<div class="row">
	                      <center>
	                        <div class="col-md-12 col-sm-12  form-group">
	                          <img src="<?php echo base_url() ?>Assets/images/head.png" width = "50%">
	                        </div>
	                        <div class="col-md-12 col-sm-12  form-group">
	                          Blagungan, Donoyudan, Kalijambe, Sragen <br>
	                          Telp: +62-813-2905-3139 | Website : https://smk-aliska.sch.id/ | Email : sekretariat@smk-aliska.sch.id, smkalislamkalijambe@gmail.com
	                          <hr>
	                        </div>
	                        <div class="col-md-12 col-sm-12  form-group">
	                        	<center>
	                        		<h3>
		                        		LAPORAN PENERIMAAN PEMBAYARAN SISWA
		                        	</h3>
		                        	Periode : <?php echo explode(',', $oParam)[0] ?> - <?php echo explode(',', $oParam)[1] ?>
	                        	</center>
	                        </div>
	                      </center>
	                    </div>
		            	<!-- COntent Fill -->
		            	<div class="col-md-12 col-sm-12  form-group">
		            		<div class="dx-viewport demo-container">
		                        <div id="data-grid-demo">
		                          <div id="gridContainer">
		                          </div>
		                        </div>
		                    </div>
		            	</div>

	                    <div class="row">
	                    	<div class="col-md-6 col-sm-12  form-group">
	                    		<center>
	                    			<b>Yang Membuat</b> <br><br><br>
	                          		Bety Rosiana, S.Pd.
	                    		</center>
	                        </div>
	                        <div class="col-md-6 col-sm-12  form-group">
	                    		<center>
	                    			<b>Yang Menerima</b> <br><br><br>
	                          		Santrinitas Yulia DR, S.Pd., M.Pd.
	                    		</center>
	                        </div>
	                    </div>
		            </div>

		        </div>
		    </div>
		</div>

	</div>
</div>



<script type="text/javascript">
	$(function () {
		$(document).ready(function () {
			var FromDate = '<?php echo explode(',', $oParam)[0] ?>';
			var ToDate = '<?php echo explode(',', $oParam)[1] ?>';
			// console.log(FromDate);
			$.ajax({
				type: "post",
			    url: "<?=base_url()?>C_Report/pembayaransiswa",
			    data: {'TglAwal':FromDate, 'TglAkhir':ToDate},
			    dataType: "json",
			    success: function (response) {
			    	var items_data = [];

                    for (var i = 0; i < response.data.length; i++) {
                      var arr ={
                          KodePosKeuangan     : response.data[i].KodePosKeuangan,
                          NamaPos     : response.data[i].NamaPos,
                          NIS      : response.data[i].NIS,
                          NamaSiswa : response.data[i].NamaSiswa,
                          NamaKelas : response.data[i].NamaKelas,
                          NamaJurusan   : response.data[i].NamaJurusan,
                          ConcatKelas   : response.data[i].ConcatKelas,
                          NoTransaksi   : response.data[i].NoTransaksi,
                          TglTransaksi   : response.data[i].TglTransaksi,
                          Keterangan   : response.data[i].Keterangan,
                          Jumlah   : parseFloat(response.data[i].Jumlah),
                      }
                      items_data.push(arr);
                    }
			      bindGrid(items_data);
			    }
			});
		});

		function bindGrid(data) {
			$("#gridContainer").dxDataGrid({
				allowColumnResizing: true,
	            dataSource: data,
	            keyExpr: "KodePosKeuangan",
	            showBorders: true,
	            allowColumnReordering: true,
	            allowColumnResizing: true,
	            columnAutoWidth: true,
	            showBorders: true,
	            grouping: {
			      autoExpandAll: true,
			    },
	            export: {
	                enabled: true,
	                fileName: "Daftar Jurusan"
	            },
	            columns: [
	            	{
	                    dataField: "NIS",
	                    caption: "No. Induk",
	                },
	                {
	                    dataField: "NamaSiswa",
	                    caption: "Nama Siswa",
	                },
	            	{
	                    dataField: "NoTransaksi",
	                    caption: "NoTransaksi",
	                },
	                {
	                    dataField: "TglTransaksi",
	                    caption: "Tgl Transaksi",
	                },
	                {
	                    dataField: "Keterangan",
	                    caption: "Keterangan",
	                },
	                {
	                    dataField: "Jumlah",
	                    caption: "Jumlah",
	                    format: 'fixedPoint',
	                },
	                {
	                    dataField: "NamaPos",
	                    caption: "Nama Pos Keuangan",
	                    groupIndex: 0,
	                },
	                {
	                    dataField: "ConcatKelas",
	                    caption: "Kelas",
	                    groupIndex: 1,
	                },
	            ],
	            summary:{
	            	groupItems: [
	            		{
				        column: 'Jumlah',
				        summaryType: 'sum',
				        valueFormat : 'fixedPoint',
				        displayFormat: 'Total: {0}',
				        showInGroupFooter: false,
				        alignByColumn: true,
				      }
	            	]
	            }
			});
		}
	});
</script>



<?php
	require_once(APPPATH."views/parts/Footer.php");
?>