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
</style>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">

    <div class="clearfix"></div>
      <div class="row">
        <div class="col-md-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Bukti Pembayaran <small>SMK Al-Islam Kalijambe</small></h2>
              <div class="clearfix"></div>
            </div>

            <div class="x_content">
    <?php 
      $SQL = "
        SELECT 
          a.NoTransaksi,
          a.TglTransaksi,
          TIME(a.TglPencatatan) JamTransaksi,
          e.NIS,
          e.NamaSiswa,
          CONCAT(f.NamaKelas, ' - ', g.NamaJurusan) Kelas,
          CONCAT(b.NamaItemTagihan, ' - ', fnGetMonthName(MONTH(d.TglTagihan)),' ' , YEAR(TglTagihan)) Keterangan,
          b.Jumlah,
          a.CreatedOn,
          a.CreatedBy
        FROM pembayaranheader a
        LEFT JOIN pembayarandetail b on a.NoTransaksi = b.NoTransaksi
        LEFT JOIN tagihandetail c on b.BaseNum = c.NoTransaksi and b.BaseLine = c.LineNumber
        LEFT JOIN tagihanheader d on c.NoTransaksi = d.NoTransaksi
        LEFT JOIN tsiswa e on d.NISSiswa = e.NIS
        LEFT JOIN tkelas f on e.Kelas = f.id
        LEFT JOIN tjurusan g on e.Jurusan = g.id
        WHERE d.NoTransaksi = '".$NoTransaksi."'
      ";

      $rs = $this->db->query($SQL);
      $htmlTag = '';
      if ($rs->num_rows() > 0) {
        foreach ($rs->result() as $key) {
          $htmlTag .= '
                  <section class="content invoice">
                    <div class="row">
                      <center>
                        <div class="col-md-12 col-sm-12  form-group">
                          <img src="'.base_url().'Assets/images/head.png" width = "50%">
                        </div>
                        <div class="col-md-12 col-sm-12  form-group">
                          Blagungan, Donoyudan, Kalijambe, Sragen <br>
                          Telp: +62-813-2905-3139 | Website : https://smk-aliska.sch.id/ | Email : sekretariat@smk-aliska.sch.id, smkalislamkalijambe@gmail.com
                          <hr>
                        </div>
                      </center>
                    </div>

                    <div class="row invoice-info">
                      <div class="col-sm-6 invoice-col">
                        <center>
                          <table>
                            <tr>
                              <td> NO TRANSAKSI </td>
                              <td> : </td>
                              <td> '.$key->NoTransaksi.' </td>
                            </tr>
                            <tr>
                              <td> TANGGAL </td>
                              <td> : </td>
                              <td> '.$key->TglTransaksi.' </td>
                            </tr>
                            <tr>
                              <td> JAM TRX </td>
                              <td> : </td>
                              <td> '.$key->JamTransaksi.' </td>
                            </tr>
                          </table>
                        </center>
                      </div>

                      <div class="col-sm-6 invoice-col">
                        <center>
                          <table>
                            <tr>
                              <td> NIS </td>
                              <td> : </td>
                              <td> '.$key->NIS.' </td>
                            </tr>
                            <tr>
                              <td> NAMA SISWA </td>
                              <td> : </td>
                              <td> '.$key->NamaSiswa.' </td>
                            </tr>
                            <tr>
                              <td> KELAS </td>
                              <td> : </td>
                              <td> '.$key->Kelas.' </td>
                            </tr>
                          </table>
                        </center>
                      </div>
                    </div>
                    <br>

                    <div class="row">
                      <div class="  table">
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>No</th>
                              <th style="width: 65%">Keterangan Pembayaran</th>
                              <th>Jumlah (Rp. )</th>
                            </tr>
                          </thead>
                          <tbody>
          ';
          $SQLDetail = "
            SELECT 
              CONCAT(a.NamaItemTagihan, ' - ', fnGetMonthName(MONTH(c.TglTagihan)),' ' , YEAR(c.TglTagihan)) Keterangan,
              a.Jumlah
            FROM pembayarandetail a
            LEFT JOIN tagihandetail b on a.BaseNum = b.NoTransaksi and a.BaseLine = b.LineNumber
            LEFT JOIN tagihanheader c on c.NoTransaksi = b.NoTransaksi
            WHERE a.NoTransaksi = '".$key->NoTransaksi."'
          ";

          $detailRS = $this->db->query($SQLDetail);
          // var_dump($detailRS->num_rows());
          $Row = 0;
          $TotalTransaksi = 0;
          foreach ($detailRS->result() as $keyDetail) {
            $Row += 1;
            $TotalTransaksi += $keyDetail->Jumlah;
            $htmlTag .= '
                            <tr>
                              <td>'.$Row.'</td>
                              <td>'.$keyDetail->Keterangan.'</td>
                              <td>'.number_format($keyDetail->Jumlah).'</td>
                            </tr>
            ';
          }
          $htmlTag .= '
                            </tbody>
                        </table>
                      </div>
                    </div>

                    <div class="row">

                      <div class="col-md-6">
                        <table class="table">
                          <tbody>
                            <tr>
                              <th style="width:100%">Terbilang:</th>
                              <td></td>
                            </tr>
                            <tr>
                              <th style="width:100%">'.number_to_words($TotalTransaksi).' Rupiah</th>
                              <td></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div class="col-md-6">
                        <table class="table">
                          <tbody>
                            <tr>
                              <th style="width:45%">Subtotal:</th>
                              <td>Rp. '.number_format($TotalTransaksi).'</td>
                            </tr>
                          </tbody>
                        </table>

                        <center>
                        Sragen, '.date('d F y').' <br>
                        Yang Menerima, <br><br><br>
                        '.$key->CreatedBy.'<br>
                        '.$key->CreatedOn.'
                        </center>
                      </div>
                    </div>
                  </section>
                  <hr style = "border-top: 1px dashed  red">
          ';
        }
      }

      echo $htmlTag;
    ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?php
  require_once(APPPATH."views/parts/Footer.php");
?>