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
              <h2>Lokasi Patroli</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
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
  <!-- /page content -->

  <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true" id="modal_">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Modal Lokasi Patroli</h4>
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="post_" data-parsley-validate class="form-horizontal form-label-left">
            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nama Lokasi <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="NamaArea" id="NamaArea" required="" placeholder="Nama Lokasi" class="form-control ">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="formtype" id="formtype" value="add">
                <input type="hidden" name="RecordOwnerID" id="RecordOwnerID" value="<?php echo $this->session->userdata('RecordOwnerID') ?>">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Alamat <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="AlamatArea" id="AlamatArea" required="" placeholder="Alamat" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Keterangan <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="text" name="Keterangan" id="Keterangan" required="" placeholder="Keterangan" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Jam Mulai Patroli <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="time" name="StartPatroli" id="StartPatroli" required="" placeholder="Mulai Patroli" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Interval <span class="required">*</span>
              </label>
              <div class="col-md-3 col-sm-3  form-group">
                <input type="number" name="IntervalPatroli" id="IntervalPatroli" required="" placeholder="Interval" class="form-control ">
              </div>
              <div class="col-md-3 col-sm-3  form-group">
                <select class="form-control" id="IntervalType" name="IntervalType">
                  <option value="DAY">Hari</option>
                  <option value="HOUR">Jam</option>
                  <!-- <option value="MINUTE">Menit</option> -->
                </select>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Jam Selesai Patroli <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="time" name="EndPatroli" id="EndPatroli" required="" placeholder="Selesai Patroli" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Toleransi <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 ">
                <input type="number" name="Toleransi" id="Toleransi" required="" placeholder="Toleransi. *Menit" class="form-control ">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align">Latitude</label>
              <div class="col-md-6 col-sm-6">
                <input type="text" name="Latitude" id="Latitude" class="form-control" placeholder="Klik peta atau isi manual">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align">Longitude</label>
              <div class="col-md-6 col-sm-6">
                <input type="text" name="Longitude" id="Longitude" class="form-control" placeholder="Klik peta atau isi manual">
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align">Radius</label>
              <div class="col-md-6 col-sm-6">
                <input type="range" id="RadiusSlider" min="50" max="1000" step="10" value="100" class="form-control" style="padding:0; height:auto;">
                <input type="hidden" name="Radius" id="Radius" value="100">
                <small>Radius: <span id="RadiusLabel">100</span> meter</small>
              </div>
            </div>

            <div class="item form-group">
              <label class="col-form-label col-md-3 col-sm-3 label-align">Peta</label>
              <div class="col-md-9 col-sm-9">
                <!-- Search box -->
                <div class="input-group" style="margin-bottom:6px; position:relative;">
                  <input type="text" id="mapSearchInput" class="form-control" placeholder="Cari alamat atau nama tempat..." autocomplete="off">
                  <span class="input-group-btn">
                    <button class="btn btn-default" type="button" id="btnMapSearch">
                      <i class="fa fa-search"></i> Cari
                    </button>
                  </span>
                  <ul id="mapSearchResults" style="display:none; position:absolute; top:38px; left:0; right:0; z-index:9999; background:#fff; border:1px solid #ccc; border-radius:0 0 4px 4px; list-style:none; margin:0; padding:0; max-height:200px; overflow-y:auto;"></ul>
                </div>
                <!-- Map -->
                <div id="mapPicker" style="height:350px; border:1px solid #ccc; border-radius:4px;"></div>
                <small class="text-muted">Klik pada peta untuk menentukan titik koordinat</small>
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
<?php
  require_once(APPPATH."views/parts/Footer.php");
?>
<script src="https://cdn.jsdelivr.net/timepicker.js/latest/timepicker.min.js"></script>
<link href="https://cdn.jsdelivr.net/timepicker.js/latest/timepicker.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script type="text/javascript">
  $(function () {
    var RecordOwnerID = $('#RecordOwnerID').val();
    $(document).ready(function () {
      // var timepicker = new TimePicker('StartPatroli', {
      //   lang: 'en',
      //   theme: 'dark'
      // });
      // timepicker.on('change', function(evt) {
        
      //   var value = (evt.hour || '00') + ':' + (evt.minute || '00');
      //   evt.element.value = value;

      // });

      // var timepickerend = new TimePicker('EndPatroli', {
      //   lang: 'en',
      //   theme: 'dark'
      // });
      // timepickerend.on('change', function(evt) {
        
      //   var value = (evt.hour || '00') + ':' + (evt.minute || '00');
      //   evt.element.value = value;

      // });

      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_LokasiPatroli/Read",
        data: {'id':'', 'RecordOwnerID': RecordOwnerID},
        dataType: "json",
        success: function (response) {
          bindGrid(response.data);
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
            url     : '<?=base_url()?>C_LokasiPatroli/CRUD',
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
    $('.close').click(function() {
      location.reload();
    });

    // ===== MAP PICKER =====
    var map, marker, circle;

    function initMap(lat, lng, radius) {
      lat    = (lat && !isNaN(lat)) ? lat : -6.2088;
      lng    = (lng && !isNaN(lng)) ? lng : 106.8456;
      radius = radius || 100;
      var hasCoord = (parseFloat($('#Latitude').val()) && parseFloat($('#Longitude').val()));

      if (map) { map.remove(); map = null; marker = null; circle = null; }

      map = L.map('mapPicker').setView([lat, lng], 15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      if (hasCoord) {
        placeMarker(lat, lng, radius);
      }

      map.on('click', function(e) {
        placeMarker(e.latlng.lat, e.latlng.lng, parseInt($('#RadiusSlider').val()));
      });
    }

    function placeMarker(lat, lng, radius) {
      if (marker) map.removeLayer(marker);
      if (circle) map.removeLayer(circle);
      marker = L.marker([lat, lng]).addTo(map);
      circle = L.circle([lat, lng], { radius: radius, color: '#3085d6', fillOpacity: 0.15 }).addTo(map);
      $('#Latitude').val(lat.toFixed(8));
      $('#Longitude').val(lng.toFixed(8));
      $('#Radius').val(radius);
      map.setView([lat, lng], 15);
    }

    $(document).on('input', '#RadiusSlider', function() {
      var r = parseInt($(this).val());
      $('#RadiusLabel').text(r);
      $('#Radius').val(r);
      if (circle && marker) { circle.setRadius(r); }
    });

    $(document).on('change', '#Latitude, #Longitude', function() {
      var lat = parseFloat($('#Latitude').val());
      var lng = parseFloat($('#Longitude').val());
      if (!isNaN(lat) && !isNaN(lng)) {
        placeMarker(lat, lng, parseInt($('#RadiusSlider').val()));
      }
    });

    $('#modal_').on('shown.bs.modal', function() {
      var lat = parseFloat($('#Latitude').val()) || null;
      var lng = parseFloat($('#Longitude').val()) || null;
      var r   = parseInt($('#Radius').val()) || 100;
      initMap(lat, lng, r);
    });
    // ===== MAP SEARCH (Nominatim) =====
    function doMapSearch() {
      var q = $.trim($('#mapSearchInput').val());
      if (!q) return;

      $('#btnMapSearch').html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
      $('#mapSearchResults').hide().empty();

      $.ajax({
        url: 'https://nominatim.openstreetmap.org/search',
        data: { q: q, format: 'json', limit: 6, addressdetails: 1 },
        headers: { 'Accept-Language': 'id' },
        dataType: 'json',
        success: function(results) {
          if (!results || results.length === 0) {
            $('#mapSearchResults').html('<li style="padding:8px 12px; color:#999;">Lokasi tidak ditemukan</li>').show();
          } else {
            $.each(results, function(i, item) {
              var li = $('<li>')
                .text(item.display_name)
                .css({ padding: '8px 12px', cursor: 'pointer', borderBottom: '1px solid #eee', fontSize: '13px' })
                .hover(function(){ $(this).css('background','#f5f5f5'); }, function(){ $(this).css('background','#fff'); })
                .on('click', function() {
                  var lat = parseFloat(item.lat);
                  var lng = parseFloat(item.lon);
                  placeMarker(lat, lng, parseInt($('#RadiusSlider').val()));
                  $('#mapSearchInput').val(item.display_name);
                  $('#mapSearchResults').hide().empty();
                });
              $('#mapSearchResults').append(li);
            });
            $('#mapSearchResults').show();
          }
        },
        error: function() {
          $('#mapSearchResults').html('<li style="padding:8px 12px; color:#c00;">Gagal menghubungi layanan pencarian</li>').show();
        },
        complete: function() {
          $('#btnMapSearch').html('<i class="fa fa-search"></i> Cari').prop('disabled', false);
        }
      });
    }

    $(document).on('click', '#btnMapSearch', function() { doMapSearch(); });

    $(document).on('keypress', '#mapSearchInput', function(e) {
      if (e.which === 13) { e.preventDefault(); doMapSearch(); }
    });

    // Tutup dropdown saat klik di luar
    $(document).on('click', function(e) {
      if (!$(e.target).closest('#mapSearchInput, #mapSearchResults').length) {
        $('#mapSearchResults').hide();
      }
    });
    // ===== END MAP SEARCH =====

    // ===== END MAP PICKER =====
    function GetData(id) {
      var where_field = 'id';
      var where_value = id;
      var table = 'users';
      $.ajax({
        type: "post",
        url: "<?=base_url()?>C_LokasiPatroli/Read",
        data: {'id':id, 'RecordOwnerID':RecordOwnerID},
        dataType: "json",
        success: function (response) {
          $.each(response.data,function (k,v) {
            // $('#KodePenyakit').val(v.KodePenyakit).change;
            // console.log(v.StartPatroli)
            $('#id').val(v.id);
            $('#NamaArea').val(v.NamaArea);
            $('#AlamatArea').val(v.AlamatArea);
            $('#Keterangan').val(v.Keterangan);
            $('#RecordOwnerID').val(v.RecordOwnerID);
            $('#StartPatroli').val(v.StartPatroli.split(".")[0]);
            $('#IntervalPatroli').val(v.IntervalPatroli);
            $('#IntervalType').val(v.IntervalType);
            $('#EndPatroli').val(v.EndPatroli.split(".")[0]);
            $('#Toleransi').val(v.Toleransi);
            $('#Latitude').val(v.Latitude || '');
            $('#Longitude').val(v.Longitude || '');
            var r = parseInt(v.Radius) || 100;
            $('#RadiusSlider').val(r);
            $('#Radius').val(r);
            $('#RadiusLabel').text(r);

            $('#formtype').val("edit");
            $('#modal_').modal('show');
          });
        }
      });
    }
    function bindGrid(data) {

      $("#gridContainer").dxDataGrid({
        allowColumnResizing: true,
            dataSource: data,
            keyExpr: "id",
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
                visible: true,
                width: 240,
                placeholder: "Search..."
            },
            export: {
                enabled: true,
                fileName: "Daftar Lokasi Patroli"
            },
            columns: [
                {
                    dataField: "NamaArea",
                    caption: "Nama Lokasi",
                    allowEditing:false
                },
                {
                    dataField: "AlamatArea",
                    caption: "Alamat Lokasi",
                    allowEditing:false
                },
                {
                    dataField: "Keterangan",
                    caption: "Keterangan",
                    allowEditing:false
                },
                {
                    dataField: "FileItem",
                    caption: "Action",
                    allowEditing:false,
                    cellTemplate: function(cellElement, cellInfo) {
                      LinkAccess = "<a href = '<?=base_url()?>shift/"+cellInfo.data.id+"' class='btn btn-warning'>Atur Shift</a>";
                      // console.log();
                      cellElement.append(LinkAccess);
                  }
                }
            ],
            onEditingStart: function(e) {
                GetData(e.data.id);
            },
            onInitNewRow: function(e) {
                $('#Latitude').val('');
                $('#Longitude').val('');
                $('#RadiusSlider').val(100);
                $('#Radius').val(100);
                $('#RadiusLabel').text(100);
                $('#mapSearchInput').val('');
                $('#mapSearchResults').hide().empty();
                $('#modal_').modal('show');
            },
            onRowRemoving: function(e) {
              id = e.data.id;
              Swal.fire({
                title: 'Apakah anda yakin?',
                text: "anda akan menghapus data di baris ini !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
              }).then((result) => {
                if (result.value) {
                  var table = 'app_setting';
                  var field = 'id';
                  var value = id;

                  $.ajax({
                      type    :'post',
                      url     : '<?=base_url()?>C_LokasiPatroli/CRUD',
                      data    : {'id':id,'formtype':'delete', 'RecordOwnerID':RecordOwnerID},
                      dataType: 'json',
                      success : function (response) {
                        if(response.success == true){
                          Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                      ).then((result)=>{
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
                            location.reload();
                          });
                        }
                      }
                    });
                  
                }
                else{
                  location.reload();
                }
              })
            },
        });

        // add dx-toolbar-after
        // $('.dx-toolbar-after').append('Tambah Alat untuk di pinjam ');
    }
  });
</script>