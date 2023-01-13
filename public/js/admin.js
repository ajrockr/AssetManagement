/*
 *   Copyright (c) 2023 Anthony Rizzo

 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.

 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.

 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

// Credit:
// gaetanoM (2/5/2016)
// https://stackoverflow.com/posts/35234317/revisions
// Note: .change is deprecated; use .on('change')
$(function () {
    $('.radio').on('change',function(e) {
        e.preventDefault();
        $('.radio').not(this).prop('checked', false);
        $(this).prop('checked', true);
    });
 });
