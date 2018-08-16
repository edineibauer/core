<div class="row">
    <div class="container">
        <form method="post" action="" enctype="multipart/form-data"
              style="padding:30px; max-width: 700px; margin: 20px auto; float: initial">

            <br>
            <h4 class="center" style="padding-bottom: 50px">SINGLE FRAMEWORK</h4>
            <div class="input-field col s12">
                <input id="sitename" name="sitename" type="text" required="required" class="validate" style="font-size:1.7em">
                <label for="sitename" style="font-size:1.4em">Nome do Projeto</label>
            </div>

            <div class="file-field input-field col s12">
                <div class="btn">
                    <span>Logo</span>
                    <input type="file" name="logo"  accept="image/*">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text">
                </div>
            </div>

            <div class="file-field input-field col s12">
                <div class="btn">
                    <span>Favicon</span>
                    <input type="file" name="favicon" required="required" accept="image/*">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text">
                </div>
            </div>

            <div class="row clearfix">
                <br>
                <div class="switch col s6 m4">
                    <label>
                        HTTP
                        <input type="checkbox" name="protocol">
                        <span class="lever"></span>
                        HTTPS
                    </label>
                </div>
                <div class="switch col s6 m4 right">
                    <label class="right">
                        sem WWW
                        <input type="checkbox" name="www">
                        <span class="lever"></span>
                        com WWW
                    </label>
                </div>
            </div>

            <div class="container center">
                <button type="submit" class="waves-effect waves-light btn-large">Criar Projeto</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="start/assets/materialize-start.css" />
<script src="start/assets/jquery.js"></script>
<script src="start/assets/materialize.min.js"></script>
<script>
    $("#sitename").focus();
</script>