<?php
/*
 * This file is part of facturacion_base
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'plugins/facturacion_base/model/core/factura_cliente.php';

///En Colombia por regulación de la DIAN, se maneja un número consecutivo de factura el cual no se reinicia con el cambio de ejercicio, en ocasiones se le asigna un prefijo a la factura; esta modificación permite personalizar la númeración de la factura, desde las SERIES

/**
 * Factura de un cliente.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class factura_cliente extends FacturaScripts\model\factura_cliente
{
   public function new_codigo()
   {
      /// buscamos el número inicial para la serie
      $num = 1;
      $serie0 = new \serie();
      $serie = $serie0->get($this->codserie);
      if($serie)
      {
         /// ¿Se ha definido un nº de factura inicial para esta serie y ejercicio?
         
            $num = $serie->numfactura;
      }
      
      /// buscamos un hueco o el siguiente número disponible
      $encontrado = FALSE;
      $fecha = $this->fecha;
      $hora = $this->hora;
      $sql = "SELECT ".$this->db->sql_to_int('numero')." as numero,fecha,hora FROM ".$this->table_name
              ." WHERE codserie = ".$this->var2str($this->codserie)
              ." ORDER BY numero ASC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            if( intval($d['numero']) < $num )
            {
               /**
                * El número de la factura es menor que el inicial.
                * El usuario ha cambiado el número inicial después de hacer
                * facturas.
                */
            }
            else if( intval($d['numero']) == $num )
            {
               /// el número es correcto, avanzamos
               $num++;
            }
            else
            {
               /// Hemos encontrado un hueco y debemos usar el número y la fecha.
               $encontrado = TRUE;
               $fecha = Date('d-m-Y', strtotime($d['fecha']));
               $hora = Date('H:i:s', strtotime($d['hora']));
               break;
            }
         }
      }
      
      if($encontrado)
      {
        ///hemos encontrado un hueco
         $this->numero = $num;
         $this->fecha = $fecha;
         $this->hora = $hora;
      }
      else
      {
        ///no hemos encontrado un hueco, $num es el siguiente disponible
         $this->numero = $num;
      }
      ///aca se define la cantidad de ceros antes del número consecutivo en %06s
         $this->codigo = $this->codserie.sprintf('%06s', $this->numero);
   }
   
}
