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
		              <h2>Laporan Keuangan <small>SMK Al-Islam Kalijambe</small></h2>
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
		                        		LAPORAN KEUANGAN
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
	                          		Santrinitas Yulia DR, S.Pd., M.Pd.
	                    		</center>
	                        </div>
	                        <div class="col-md-6 col-sm-12  form-group">
	                    		<center>
	                    			<b>Menyetujui</b> <br><br><br>
	                          		Soleh Setyawan, S.Pd.
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
		        url: "<?=base_url()?>C_BukuKas/Read",
		        data: {
		          'FromDate':FromDate,
		          'ToDate':ToDate
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
		                Pemasukan     : parseFloat(response.data[i]["Pemasukan"]),
		                Pengeluaran   : parseFloat(response.data[i]["Pengeluaran"]),
		                Saldo         : parseFloat(SaldoAwal)
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
		                Pemasukan     : parseFloat(response.data[i]["Pemasukan"]),
		                Pengeluaran   : parseFloat(response.data[i]["Pengeluaran"]),
		                Saldo         : parseFloat(SaldoAkhir)
		              }
		            }
		            // console.log()
		            items_data.push(arr);
		          }
		          // console.log(items_data);
		          bindGrid(items_data);
		        }
		      });
		});

		function bindGrid(data) {
			$("#gridContainer").dxDataGrid({
				allowColumnResizing: true,
	            dataSource: data,
	            keyExpr: "KodeAkun",
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
	                {
	                    dataField: "Pemasukan",
	                    caption: "Pemasukan",
	                    allowEditing:false,
	                    format: 'fixedPoint',
	                },
	                {
	                    dataField: "Pengeluaran",
	                    caption: "Pengeluaran",
	                    allowEditing:false,
	                    format: 'fixedPoint',
	                },
	                {
	                    dataField: "Saldo",
	                    caption: "Saldo",
	                    allowEditing:false,
	                    format: 'fixedPoint',
	                },
	            ],
	            summary:{
	            	totalItems: [
		            	{
					        column: 'Pemasukan',
					        summaryType: 'sum',
					        valueFormat : 'fixedPoint',
					        displayFormat: 'Total: {0}',
					        showInGroupFooter: true,
					        alignByColumn: true,
					    },
					    {
					        column: 'Pengeluaran',
					        summaryType: 'sum',
					        valueFormat : 'fixedPoint',
					        displayFormat: 'Total: {0}',
					        showInGroupFooter: true,
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