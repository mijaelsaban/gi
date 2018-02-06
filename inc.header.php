<div id="header">
	<div class="page">
        <?php $seccion=''; ?>
    	<p id="branding"><a href="inicio"<?=($seccion=='inicio')?' class="active"':'';?>>Grupo Impessa</a></p>
       	<p id="integradores">Integradores de <a href="http://www.nice.com/" target="_blank" id="intNice">Qognifyi</a> y <a href="http://www.aliara.com/" target="_blank" id="intAliara">Aliara</a></p>
        <ul id="nav">
            <li><a href="quienes-somos"<?=($seccion=='quienes-somos')?' class="active"':'';?>>Quienes Somos</a></li>
            <li>
            	<a href="cercos-perimetrales-electrificados"<?=($seccion=='cercos-perimetrales-electrificados')?' class="active"':'';?>>Sistemas de Seguridad</a>
                <ul>
                	<li><a href="cercos-perimetrales-electrificados"<?=($seccion=='cercos-perimetrales-electrificados')?' class="active"':'';?>>Cercos Perimetrales Electrificados</a></li>
                    <li><a href="cercos-perimetrales-sensor-microfonico"<?=($seccion=='cercos-perimetrales-sensor-microfonico')?' class="active"':'';?>>Cercos Perimetrales Sensor Microfonico</a></li>
                    <li><a href="sistema-antientraderas"<?=($seccion=='sistema-antientraderas')?' class="active"':'';?>>Sistema Anti Entradera</a></li>
                    <li><a href="camara-de-seguridad"<?=($seccion=='camara-de-seguridad')?' class="active"':'';?>>Cámaras de Seguridad</a></li>
                </ul>
            </li>
            <li><a href="insumos.php"<?=($seccion=='insumos')?' class="active"':'';?>>Insumos</a></li>
            <li><a href="galeria.php"<?=($seccion=='galeria')?' class="active"':'';?>>Galería de Fotos</a></li>
            <li><a href="contacto"<?=($seccion=='contacto')?' class="active"':'';?>>Contacto</a></li>
        </ul>
        <a href="#" id="bt_nav">Menu</a>
    </div>
</div>