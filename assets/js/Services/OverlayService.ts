import LoaderOverlayService from '@wexample/symfony-loader/js/Services/OverlayService';

/**
 * The loader's overlay service, wearing this design system's backdrop.
 *
 * Only showStandalone() needs it — an overlay opened by a component brings its
 * own element — so an application that never calls it could keep the base;
 * registering this one costs nothing and spares the question.
 */
export default class OverlayService extends LoaderOverlayService {
  public static componentPath: string = '@WexampleSymfonyDesignSystemBundle/components/overlay';
}
