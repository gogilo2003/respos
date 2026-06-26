import * as heroIcons from '@heroicons/vue/24/outline';

export const useHeroIcon = (name: string) => {
    const iconName = `${name
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join('')}Icon`;

    try {
        const icon = heroIcons[iconName as keyof typeof heroIcons];
        if (!icon) throw new Error('Icon not found');
        return icon;
    } catch (error) {
        console.error(`Icon ${iconName} not found.`);
        return null;
    }
};
