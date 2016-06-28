<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace hrzg\filefly\plugins;

use hrzg\filefly\models\FileflyHashmap;
use hrzg\filefly\Module;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;
use yii\base\Component;


/**
 * Class UpdatePermission
 * @package hrzg\filefly\plugins
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class UpdatePermission extends Component implements PluginInterface
{
    /**
     * The yii component name of this filesystem
     * @var string
     */
    public $component;

    /**
     * @var FilesystemInterface $filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    //    protected $permissions = [];

    /**
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'updatePermission';
    }

    /**
     * Build permissions array for multi selects in permission modal
     * // TODO optimize iterations!?
     *
     * @param array $item
     * @param string $path
     *
     * @return bool
     */
    public function handle($item, $path)
    {
        /** @var $hash \hrzg\filefly\models\FileflyHashmap */
        $query = FileflyHashmap::find();
        $query->andWhere(['component' => $this->component]);
        $query->andWhere(['path' => $path]);
        $hash = $query->one();

        if ($hash === null) {
            return false;
        } else {

            $readItems = [];
            foreach ($item['authRead'] as $readItem) {
                $readItems[$readItem['role']] = $readItem['role'];
            }

            $updateItems = [];
            foreach ($item['authUpdate'] as $updateItem) {
                $updateItems[$updateItem['role']] = $updateItem['role'];
            }

            $deleteItems = [];
            foreach ($item['authDelete'] as $deleteItem) {
                $deleteItems[$deleteItem['role']] = $deleteItem['role'];
            }

            $hash->authItemArrayToString(Module::ACCESS_READ, $readItems);
            $hash->authItemArrayToString(Module::ACCESS_UPDATE, $updateItems);
            $hash->authItemArrayToString(Module::ACCESS_DELETE, $deleteItems);

            if (!$hash->save()) {
                \Yii::error('Could not update item [' . $path . '] in hash table!', __METHOD__);
                return false;
            }
            return true;
        }
    }
}
