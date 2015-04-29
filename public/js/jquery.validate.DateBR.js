/* 
 * Copyright (C) 2015 Irving Fernando de Medeiros Oliveira
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
jQuery.validator.addMethod("dateBR", function(value, element) {   
     //contando chars   
    if(value.length!=10) return false;   
    // verificando data   
    var data        = value;   
    var dia         = data.substr(0,2);   
    var barra1      = data.substr(2,1);   
    var mes         = data.substr(3,2);   
    var barra2      = data.substr(5,1);   
    var ano         = data.substr(6,4);
    
    var dataAtual   = new Date();
    var dataInformada = new Date(ano, mes -1, dia);
    
    if(data.length!=10||barra1!="/"||barra2!="/"||isNaN(dia)||isNaN(mes)||isNaN(ano)||dia>31||mes>12)return false;   
    if((mes==4||mes==6||mes==9||mes==11)&& dia==31)return false;   
    if(mes==2 && (dia>29||(dia==29 && ano%4!=0)))return false;   
    if(ano < 1900)return false;   
    if(dataInformada > dataAtual)return false;
    return true;   
}, "Informe uma data válida");