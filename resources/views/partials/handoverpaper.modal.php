                        <!-- Modal -->
                        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                            <div class="modal-body">
                            <div id="modal">

                            <h1 style="text-align: center;">CÔNG TY TNHH SGS VIỆT NAM</h1>
                                <p style="text-align: center;">Tầng 7, 9, 10 Toà nhà VTC  18 Tam Trinh, Phường Minh Khai, Quận Hai Bà Trưng, Hà Nội</p>
                                <h2 style="text-align: center;">Handover Paper</h2>
                                <p style="text-align: center;">Số: 1234</p>
                                <table>
                                    <tr>
                                        <th>Handover Details</th>
                                        <th>Information</th>
                                    </tr>
                                    <tr>
                                        <td>Date of Handover:</td>
                                        <td>[Date, e.g., October 10, 2023]</td>
                                    </tr>
                                    <tr>
                                        <td>Handover From:</td>
                                        <td>Tungpx</td>
                                    </tr>
                                    <tr>
                                        <td>Handover To:</td>
                                        <td>Tungpx</td>
                                    </tr>
                                    <tr>
                                        <td>Reason for Handover:</td>
                                        <td>[Reason for handover, e.g., End of project, Change in management]</td>
                                    </tr>
                                </table>

                                <h2>List of Items Handed Over</h2>
                                <table>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Description</th>
                                    </tr>
                                    <tr>
                                        <td>{{ $asset->name}}</td>
                                        <td>1</td>
                                        <td>[Description of Item]</td>
                                    </tr>
                                    <!-- Add more items here -->
                                </table>

                                <h2>Additional Notes</h2>
                                <p>[Additional notes about the handover process or any other relevant information]</p>

                                <div class="signatures">
                                    <div>
                                        <p><b>NGƯỜI GIAO</b></p>
                                        <p>Tungpx</p>
                                    </div>
                                    <div></div> <!-- This is an empty div to create the space in the middle -->
                                    <div>
                                        <p><b>NGƯỜI NHẬN</b></p>
                                        <p>Tungpx</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <h4 style="padding-top: 10px">{{trans('general.sign_tos')}}</h4>
                                        <div id="signature-pad" class="m-signature-pad">
                                            <div class="m-signature-pad--body col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                                <canvas></canvas>
                                            <div class="m-signature-pad--title--signer">123</div>
                                            <div class="m-signature-pad--title--signer2">123</div>
                                                <input type="hidden" name="signature_output" id="signature_output">
                                            </div>
                                         
                                                <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                                    <button type="button" class="btn btn-sm btn-default clear" data-action="clear" id="clear_button">{{trans('general.clear_signature')}}</button>
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-6">
                                <button type="button" id="print-button" class="btn btn-default">Print</button>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                <input type="file" id="pdf-file" name="pdf-file" accept=".pdf">
                                </div>

                                <div class="box-footer text-right">
                                <button type="button" class="btn btn-primary" id="gerarPDF">Upload PDF</button>
                                </div><!-- /.box-footer -->

                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                            </div>
                        </div>
                        </div>