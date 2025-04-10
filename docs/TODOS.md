TODOS
=====

Replace module images in `resources` : to create icon, download an SVG and edit it with Inkscape :
- add a layer and put it in the background
- create a polygon with 4 corners and round value to 0,150
- export to PNG 1024x1024 px

Create migrations : `php yii migrate/create initial --migrationPath='@app/modules/alt-notification/migrations'`, in safeUp():
```
        $this->safeCreateTable('module_model', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'object_model' => $this->string(100)->notNull(),
            'object_id' => $this->integer(11)->notNull(),
            'note' => $this->text(),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(11),
            'updated_at' => $this->dateTime(),
            'updated_by' => $this->integer(11),
        ], '');
        
        // Add indexes on columns for speeding where operations ; last param (true) must be false if values (or values combinaisons if several columns) are not unique 
        $this->safeCreateIndex('idx-module_model', 'module_model', ['user_id'], true);
        // Add foreign keys (if related to a table, when deleted in this table, related rows are deleted to, but beforeDelete() and afterDelete() are not called)
        $this->safeAddForeignKey('fk-module_model-user', 'module_model', 'user_id', 'user', 'id', 'CASCADE');
```

Enable module in /admin/module/list

Create messages : `php yii message/extract-module alt-notification`
Auto translate in all languages

Add screenshots in `resources`

Update `module.json`

Once the models are created, if some of them extends ActiveRecord, update `const NON_CONTENT_CLASSES` in move-content and saas modules

See `CUZY.APP/TODO when adding a new module.md`
