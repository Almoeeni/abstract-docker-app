{knit "header"}
<div class="row">
    <div class="col-12">
        <div class="text-right mb-3">
            <a href="{$page.authRoot}staff/create_admin" class="btn btn-outline-danger">
                <i class="mdi mdi-shield-plus mr-2"></i>Create Author
            </a>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-table mr-2"></i>Author List
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col" class="text-right">ID</th>

                        <th scope="col" class="text-center">E-mail Address</th>
                        <th scope="col" class="text-center">Author</th>
                        <th scope="col" class="text-center">Book Name</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td class="text-center">1</td>

                        <td class="text-left"><a">admin@gmailcom</a></td>
                        <td class="text-center>
                           Harry
                        </td>
                        <td class="text-center">
                            <span class="text-muted">Enabled</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">

                                <a href=""
                                   class="btn btn-sm btn-outline-dark" data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="Edit Administrator"><i class="mdi mdi-shield-edit"></i></a>

                                <a href=""
                                   class="btn btn-sm btn-outline-dark" data-toggle="tooltip" data-placement="bottom"
                                   title="Activity Log"><i
                                        class="mdi mdi-table"></i></a>
                            </div>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{knit "footer"}
