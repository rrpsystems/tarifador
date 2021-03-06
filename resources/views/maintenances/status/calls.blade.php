@extends('adminlte::page')

@section('title', 'Manutenções')

@section('content_header')
    <div class="container-fluid">
        <div class="d-flex justify-content-end">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Manutenções</a></li>
                <li class="breadcrumb-item"><a href="#">Status</a></li>
                <li class="breadcrumb-item active">Ligações</li>
            </ol>     
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-primary">
                <div class="card-header no-border">
                    <div class="d-flex bd-highlight">
                        <div class="mr-auto p-2 bd-highlight">
                            <h3 class="card-title">Ligações Com Erros</h3>
                        </div>
                        <div class="p-2 bd-highlight">
                            <div class="row">
                                <a class="btn btn-outline-success btn-sm" href="{{ route('rebilling.index','billing=errors') }}">
                        		    Retarifar
                                </a>
                            </div>
                        </div>
                        <div class="p-2 bd-highlight">
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('status.index') }}">
                        	    Voltar
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                <div class="table-responsive">
                        <table class="table no-wrap table-sm table-striped table-valign-middle">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>PBX</th>
                                    <th>Direção</th>
                                    <th>Ramal</th>
                                    <th>Tronco</th>
                                    <th>DDR</th>
                                    <th>Numero Discado</th>
                                    <th>Numero Sistema</th>
                                    <th>Prefixo</th>
                                    <th>Duração</th>
                                    <th>Erro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($calls as $call)
                                
                                <tr>
                                        <td>{{ $call->id }}</td>
                                        <td> {{ date('d/m/Y', strtotime($call->calldate)) }} </td>
                                        <td> {{ date('H:i:s', strtotime($call->calldate)) }} </td>
                                        <td> {{ $call->pbx }} </td>
                                        <td> @lang("calls.$call->direction")</td>
                                        <td> {{ $call->extensions_id }} </td>
                                        <td> {{ $call->trunks_id }} </td>
                                        <td> {{ $call->did }} </td>
                                        <td> {{ $call->dialnumber }} </td>
                                        <td> {{ $call->callnumber }} </td>
                                        <td> {{ $call->prefix_id }} </td>
                                        <td> {{ gmdate("H:i:s", $call->billsec) }} </td>
                                        <td> {{ status($call->status_id) }} </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center"> Não foram encontrados dados para exibição!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="d-flex bd-highlight">
                        <div class="mr-auto p-2 bd-highlight">
                        </div>
                        <div class="p-2 bd-highlight">
					    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
 
  $(function () {
        $('.select-extensions').select2({
            placeholder: "Selecione Ramais"
        })

    });

    $(document).ready(function(){
    
        $("#delete :input").prop("disabled", true);
        $("#show :input").prop("disabled", true);
    });

    $("#checkbox").click(function(){
        if($("#checkbox").is(':checked') ){
            $("#extensions > optgroup > option").prop("selected","selected");
            $("#extensions").trigger("change");
        }else{
            $("#extensions > optgroup > option").prop("selected","");
            $("#extensions").trigger("change");
        }
    });

</script>
@stop
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/css/icheck/icheck-bootstrap.min.css') }}">
@stop