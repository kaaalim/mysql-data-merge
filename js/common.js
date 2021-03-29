var DataMerge = {
  MySQL: {
    tables: [],
    tableOffset: 0,
    errors: 0,
    limit: 100,
    timeout: 2000,
    mergeData: function(table, columns, offset) {

      $.ajax({
        url:  'api/merge/data',
        type: 'POST',
        data: {
          table: table,
          columns: columns,
          offset: offset,
          limit: DataMerge.MySQL.limit,
        },
        success: function(response) {

          if (response.success) {

            $(response.message).each(function() {

              if (this.update && response.offset && response.offset > DataMerge.MySQL.limit) {

                $('#log').find('.log-item:first').html(
                  $('<div/>', {
                    'class': this.label
                  }).append(this.text)
                );

              } else {
                $('#log').prepend(
                  $('<div/>', {
                    'class': 'log-item'
                  }).append(
                    $('<div/>', {
                      'class': this.label,
                    }).append(this.text))
                );
              }
            });

          } else {

            DataMerge.MySQL.errors++;

            $(response.message).each(function() {
              $('#log').prepend(
                $('<div/>', {
                  'class': 'log-item'
                }).append(
                  $('<div/>', {
                    'class': 'text-danger',
                  }).append(response.message)
                )
              );
            });
          }

          if (DataMerge.MySQL.tableOffset in DataMerge.MySQL.tables) {

              setTimeout(function() {

                if (response.offset && response.offset >= DataMerge.MySQL.limit) {

                  DataMerge.MySQL.mergeData(DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['table'], DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['columns'], response.offset);

                } else {

                  DataMerge.MySQL.tableOffset++;
                  if (DataMerge.MySQL.tableOffset in DataMerge.MySQL.tables) {
                    DataMerge.MySQL.mergeData(DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['table'], DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['columns'], 0);
                  } else {
                    $('#log').prepend(
                      $('<div/>', {
                        'class': 'log-item'
                      }).append(
                        $('<div/>', {
                          'class': DataMerge.MySQL.errors > 0 ? 'text-danger' : 'text-success',
                        }).append('Merging completed with <strong>' + DataMerge.MySQL.errors + '</strong> errors!'))
                    );
                  }

                }

              }, DataMerge.MySQL.timeout);

          } else {

            $('#log').prepend(
              $('<div/>', {
                'class': 'log-item'
              }).append(
                $('<div/>', {
                  'class': DataMerge.MySQL.errors > 0 ? 'text-danger' : 'text-success',
                }).append('Merging completed with <strong>' + DataMerge.MySQL.errors + '</strong> errors!'))
            );
          }
        },
        error: function (request, status, error) {

          DataMerge.MySQL.errors++;
          $('#log').prepend(
            $('<div/>', {
              'class': 'log-item'
            }).append(
              $('<div/>', {
                'class': 'text-danger',
              }).append(status)
            )
          );
        }
      });
    },
    mergeStructure: function(table, truncate) {
      $.ajax({
        url:  'api/merge/structure',
        type: 'POST',
        data: {
          table: table,
          truncate: truncate,
        },
        success: function(response) {

          DataMerge.MySQL.tableOffset++;

          if (response.success) {
            $(response.message).each(function() {
              if (this.error) {
                DataMerge.MySQL.errors++;
              }
              $('#log').prepend(
                $('<div/>', {
                  'class': 'log-item'
                }).append(
                  $('<div/>', {
                    'class': this.label,
                  }).append(this.text))
              );
            });
          } else {
            DataMerge.MySQL.errors++;
            $('#log').prepend(
              $('<div/>', {
                'class': 'log-item'
              }).append(
                $('<div/>', {
                  'class': 'text-danger',
                }).append(response.message)
              )
            );
          }

          if (DataMerge.MySQL.tableOffset in DataMerge.MySQL.tables) {

            setTimeout(function() {

              DataMerge.MySQL.mergeStructure(DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['table'], DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['truncate']);

            }, DataMerge.MySQL.timeout);

          } else {

            $('#log').prepend(
              $('<div/>', {
                'class': 'log-item'
              }).append(
                $('<div/>', {
                  'class': DataMerge.MySQL.errors > 0 ? 'text-danger' : 'text-success',
                }).append('Tables structure merged with <strong>' + DataMerge.MySQL.errors + '</strong> errors!'))
            );

            $('#log').prepend(
              $('<div/>', {
                'class': 'log-item'
              }).append(
                $('<div/>', {
                  'class': 'text-success',
                }).append('Merging tables data...')
              )
            );

            DataMerge.MySQL.tableOffset = 0;
            DataMerge.MySQL.mergeData(DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['table'], DataMerge.MySQL.tables[DataMerge.MySQL.tableOffset]['columns'], 0);

          }
        },
        error: function (request, status, error) {
          DataMerge.MySQL.errors++;
          $('#log').prepend(
            $('<div/>', {
              'class': 'log-item'
            }).append(
              $('<div/>', {
                'class': 'text-danger',
              }).append(status)
            )
          );
        }
      });
    }
  }
};
